<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Import Facade Log
use PhpOffice\PhpWord\IOFactory;

class SubmissionController extends Controller
{
    /**
     * Tampilkan halaman upload laporan untuk Mahasiswa
     */
    public function showUpload($assignmentId)
    {
        $assignment = Assignment::with('practicum')->findOrFail($assignmentId);
        return view('student.upload', compact('assignment'));
    }

    /**
     * Proses unggah file dan validasi format otomatis via PHPWord
     */
    public function store(Request $request, $assignmentId)
    {
        // Catat request awal yang masuk ke log untuk tracing data
        Log::info("Submission attempt started.", [
            'user_id' => Auth::id(),
            'assignment_id' => $assignmentId,
            'has_word' => $request->hasFile('word_file'),
            'has_pdf' => $request->hasFile('pdf_file'),
            'has_attachment' => $request->hasFile('attachment_file')
        ]);

        try {
            $assignment = Assignment::with('practicum')->findOrFail($assignmentId);
            $practicum = $assignment->practicum;
            $studentId = Auth::id();

            // 1. Validasi dinamis mengikuti mode assignment aslimu ('module' vs 'task')
            if ($assignment->type === 'module') {
                $request->validate([
                    // Tambahkan mimes: zip agar sistem LAMP membaca berkas docx kamu dengan aman
                    'word_file' => ['required', 'file', 'mimes:docx,zip', 'max:20480'],
                    'pdf_file'  => ['required', 'file', 'mimes:pdf', 'max:25600'],
                ]);
            } else {
                $request->validate([
                    'attachment_file' => ['required', 'file', 'max:30720'],
                ]);
            }

            if (now()->greaterThan($assignment->deadline)) {
                Log::warning("Submission rejected: Deadline passed.", ['assignment_id' => $assignmentId, 'user_id' => $studentId]);
                return response()->json(['message' => 'Batas waktu pengumpulan (deadline) telah terlewat.'], 422);
            }

            // Mulai Database Transaction
            return DB::transaction(function () use ($request, $assignment, $practicum, $studentId) {

                // 2. Ambil atau Buat Parent Submission
                $submission = Submission::firstOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id'    => $studentId
                    ],
                    [
                        'status' => 'pending',
                        'assigned_assistant_id' => null // Bisa di-assign dinamis nanti
                    ]
                );

                Log::info("Parent Submission resolved.", ['submission_id' => $submission->id, 'current_status' => $submission->status]);

                if ($submission->status === 'approved') {
                    return response()->json(['message' => 'Laporan telah di-ACC, tidak dapat mengunggah revisi.'], 422);
                }

                // Hitung Versi Baru
                $latestVersion = $submission->versions()->orderBy('version_number', 'desc')->first();
                $versionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

                $folderPath = "submissions/assignment_{$assignment->id}/student_{$studentId}";

                $wordPath = null;
                $pdfPath = null;
                $attachmentPath = null;
                $isFormatValid = true;
                $validationLogs = [];

                // 3. Eksekusi penyimpanan file berdasarkan tipe assignment
                if ($assignment->type === 'module') {
                    Log::info("Processing 'module' upload files into storage.");

                    $wordPath = $request->file('word_file')->store($folderPath, 'public');
                    $pdfPath  = $request->file('pdf_file')->store($folderPath, 'public');

                    Log::info("Files stored successfully.", ['word' => $wordPath, 'pdf' => $pdfPath]);

                    // Jalankan Auto-Check Format berbasis PHPWord
                    $validation = $this->validateWordFormat(Storage::disk('public')->path($wordPath), $practicum);
                    $isFormatValid = $validation['is_valid'];
                    $validationLogs = $validation['logs'];
                } else {
                    Log::info("Processing 'task' attachment upload into storage.");
                    $attachmentPath = $request->file('attachment_file')->store($folderPath, 'public');
                    Log::info("Attachment stored successfully.", ['attachment' => $attachmentPath]);
                }

                // Kembalikan status dari revision ke pending jika mahasiswa mengunggah ulang perbaikan
                if ($submission->status === 'revision') {
                    $submission->update(['status' => 'pending']);
                }

                // 4. Insert data versi ke tabel `submission_versions`
                Log::info("Attempting to insert into submission_versions table.");

                $version = SubmissionVersion::create([
                    'submission_id'          => $submission->id,
                    'version_number'         => $versionNumber,
                    'word_file_path'         => $wordPath,
                    'pdf_file_path'          => $pdfPath,
                    'is_format_valid'        => $isFormatValid,
                    'system_validation_logs' => json_encode($validationLogs), // Simpan mentah sebagai JSON String jika cast bermasalah
                    'attachment'             => $attachmentPath,
                    'assistant_notes'        => null,
                ]);

                Log::info("Submission version created successfully.", ['version_id' => $version->id, 'version_number' => $versionNumber]);

                return response()->json([
                    'message' => 'Laporan berhasil dianalisis oleh sistem!',
                    'is_format_valid' => $version->is_format_valid,
                    'version' => $version
                ], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning("Validation failed on Controller level.", ['errors' => $ve->errors()]);
            return response()->json(['message' => 'Validasi berkas gagal.', 'errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            // LOG UTAMA: Tangkap dan kunci semua error database / file permissions disini
            Log::error("FATAL ERROR IN SUBMISSION STORE FUNCTION!", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Gagal memproses unggahan laporan.',
                'error_debug' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memproses penilaian, status kelulusan, dan menyimpan JSON koordinat catatan dari Asprak
     */
    public function review(Request $request, $id)
    {
        // 1. Validasi input yang dikirim oleh Form Penilaian
        Log::info('Before update', [
            'notes' => $request->assistant_notes,
            'coords' => $request->annotation_coordinates
        ]);

        $request->validate([
            'status'                 => ['required', 'in:approved,revision'],
            'assistant_notes'        => ['required', 'string'],
            'annotation_coordinates' => ['nullable', 'string'], // Menerima JSON string dari frontend
        ]);




        try {
            // Mulai database transaction agar perubahan status parent dan versi sinkron
            return DB::transaction(function () use ($request, $id) {

                // 2. Cari data induk Submission mahasiswa
                $submission = Submission::findOrFail($id);

                // Ambil data versi dokumen paling baru yang diupload mahasiswa
                $latestVersion = $submission->versions()->orderBy('version_number', 'desc')->first();

                if (!$latestVersion) {
                    return response()->json(['message' => 'Versi berkas pengumpulan tidak ditemukan.'], 404);
                }

                // 3. Update status utama pada tabel submissions
                $submission->update([
                    'status'                => $request->status,
                    'assigned_assistant_id' => Auth::id(), // Kunci id asprak yang memeriksa
                ]);

                // 4. Validasi JSON Koordinat Keamanan sebelum dimasukkan ke database
                $rawCoordinates = $request->input('annotation_coordinates');
                // Cek jika kosong atau strings '[]', sediakan fallback array kosong
                $decoded = json_decode($rawCoordinates, true);
                $jsonToSave = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                    ? $rawCoordinates
                    : json_encode([]);

                // 5. Suntikkan nilai review ke tabel submission_versions paling baru
                $latestVersion->update([
                    'assistant_notes' => $request->assistant_notes,
                    // Pastikan kolom ini sudah ada di migration kamu (misal bertipe json atau text)
                    'annotation_coordinates' => $jsonToSave
                ]);

                // Berikan respons balik ke halaman workspace dengan membawa alert sukses
                return redirect()->back()->with('success', 'Penilaian submission berhasil disimpan ke dalam sistem.');
            });
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan review penilaian asprak.", [
                'submission_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Gagal memproses penilaian: ' . $e->getMessage()]);
        }
    }

    /**
     * Core Engine pembedah file Word (.docx) - Versi Deep Extraction
     */
    private function validateWordFormat(string $filePath, $rules): array
    {
        $isValid = true;
        $logs = [];

        try {
            Log::info("PHPWord Deep Analysis Started.", ['path' => $filePath]);

            if (!file_exists($filePath)) {
                return ['is_valid' => false, 'logs' => ['error' => 'Berkas fisik laporan tidak ditemukan di server.']];
            }

            $phpWord = IOFactory::load($filePath);
            $sections = $phpWord->getSections();

            if (empty($sections)) {
                return ['is_valid' => false, 'logs' => ['error' => 'Dokumen kosong atau tidak terbaca.']];
            }

            // Ambil Aturan Standar dari database (dengan fallback nilai default jika kosong)
            $reqTop    = $rules->required_margin_top_cm ?? 4.0;
            $reqBottom = $rules->required_margin_bottom_cm ?? 3.0;
            $reqLeft   = $rules->required_margin_left_cm ?? 4.0;
            $reqRight  = $rules->required_margin_right_cm ?? 3.0;
            $reqFont   = $rules->required_font_name ?? 'Times New Roman';
            $reqSize   = $rules->required_font_size ?? 12;
            $reqSpace  = $rules->required_line_spacing ?? 1.5;

            // 1. Cek Ukuran Margin Halaman (1 cm = 567 twips)
            $sectionStyle = $sections[0]->getStyle();
            $marginTop    = round($sectionStyle->getMarginTop() / 567, 2);
            $marginBottom = round($sectionStyle->getMarginBottom() / 567, 2);
            $marginLeft   = round($sectionStyle->getMarginLeft() / 567, 2);
            $marginRight  = round($sectionStyle->getMarginRight() / 567, 2);

            if ($marginTop != $reqTop) {
                $isValid = false;
                $logs['margin_top'] = "Margin atas terdeteksi {$marginTop} cm, seharusnya {$reqTop} cm.";
            }
            if ($marginBottom != $reqBottom) {
                $isValid = false;
                $logs['margin_bottom'] = "Margin bawah terdeteksi {$marginBottom} cm, seharusnya {$reqBottom} cm.";
            }
            if ($marginLeft != $reqLeft) {
                $isValid = false;
                $logs['margin_left'] = "Margin kiri terdeteksi {$marginLeft} cm, seharusnya {$reqLeft} cm.";
            }
            if ($marginRight != $reqRight) {
                $isValid = false;
                $logs['margin_right'] = "Margin kanan terdeteksi {$marginRight} cm, seharusnya {$reqRight} cm.";
            }

            // 2. Deep Scanning Elemen Paragraf & Text Run
            foreach ($sections[0]->getElements() as $element) {

                // Pastikan kita membedah element paragraf atau textrun
                if (method_exists($element, 'getElements')) {

                    // Cek Spasi Baris (Line Spacing) dari Paragraf
                    if (method_exists($element, 'getParagraphStyle')) {
                        $pStyle = $element->getParagraphStyle();
                        if ($pStyle) {
                            $spacing = $pStyle->getSpacingLineRule();

                            // Konversi nilai mentah twips/line rule PHPWord ke desimal standar
                            // Jika 240 = 1.0, 360 = 1.5, 480 = 2.0
                            $normalizedSpacing = 1.0;
                            if (is_numeric($spacing)) {
                                if ($spacing == 360) $normalizedSpacing = 1.5;
                                elseif ($spacing == 480) $normalizedSpacing = 2.0;
                                elseif ($spacing == 240) $normalizedSpacing = 1.0;
                                else $normalizedSpacing = round($spacing / 240, 1);
                            }

                            if ($normalizedSpacing != $reqSpace && !isset($logs['line_spacing'])) {
                                $isValid = false;
                                $logs['line_spacing'] = "Terdeteksi spasi baris {$normalizedSpacing}, standar praktikum wajib {$reqSpace}.";
                            }
                        }
                    }

                    // Ekstraksi Karakter Teks di dalam Array Elements
                    foreach ($element->getElements() as $textElement) {
                        if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {

                            // Skip jika potongan teks hanya berupa spasi kosong / enter
                            if (trim($textElement->getText()) === '') {
                                continue;
                            }

                            $fStyle = $textElement->getFontStyle();
                            if ($fStyle) {
                                $fontName = $fStyle->getName();
                                $fontSize = $fStyle->getSize();

                                // Validasi Font Name (Hanya jika terdefinisi di element teks)
                                if ($fontName && $fontName !== $reqFont && !isset($logs['font_name'])) {
                                    $isValid = false;
                                    $logs['font_name'] = "Gunakan font '{$reqFont}', terdeteksi '{$fontName}'.";
                                }

                                // Validasi Font Size (Hanya jika terdefinisi di element teks)
                                if ($fontSize && $fontSize != $reqSize && !isset($logs['font_size'])) {
                                    $isValid = false;
                                    $logs['font_size'] = "Ukuran teks terdeteksi {$fontSize}pt, standar wajib {$reqSize}pt.";
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error inside deep validateWordFormat engine.", ['msg' => $e->getMessage()]);
            $isValid = false;
            $logs['exception'] = "Gagal membedah format file: " . $e->getMessage();
        }

        return ['is_valid' => $isValid, 'logs' => $logs];
    }
}
