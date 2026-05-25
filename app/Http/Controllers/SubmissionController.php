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

            // VALIDASI
            if ($assignment->type === 'module') {
                $request->validate([
                    'word_file' => ['required', 'file', 'mimes:docx,zip', 'max:20480'],
                    'pdf_file'  => ['required', 'file', 'mimes:pdf', 'max:25600'],
                ]);
            } else {
                $request->validate([
                    'attachment_file' => ['required', 'file', 'max:30720'],
                ]);
            }

            // DEADLINE CHECK
            if (now()->greaterThan($assignment->deadline)) {
                return back()->withErrors([
                    'deadline' => 'Batas waktu pengumpulan (deadline) telah terlewat.'
                ])->withInput();
            }

            DB::transaction(function () use (
                $request,
                $assignment,
                $practicum,
                $studentId
            ) {

                // AMBIL / BUAT SUBMISSION
                $submission = Submission::firstOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id'    => $studentId
                    ],
                    [
                        'status' => 'pending',
                        'assigned_assistant_id' => null
                    ]
                );

                Log::info("Parent Submission resolved.", [
                    'submission_id' => $submission->id,
                    'current_status' => $submission->status
                ]);

                // CEK APPROVED
                if ($submission->status === 'approved') {
                    throw new \Exception('Laporan telah di-ACC, tidak dapat mengunggah revisi.');
                }

                // HITUNG VERSI
                $latestVersion = $submission->versions()
                    ->orderBy('version_number', 'desc')
                    ->first();

                $versionNumber = $latestVersion
                    ? $latestVersion->version_number + 1
                    : 1;

                $folderPath = "submissions/assignment_{$assignment->id}/student_{$studentId}";

                $wordPath = null;
                $pdfPath = null;
                $attachmentPath = null;

                $isFormatValid = true;
                $validationLogs = [];

                // MODULE
                if ($assignment->type === 'module') {

                    $wordPath = $request->file('word_file')
                        ->store($folderPath, 'public');

                    $pdfPath = $request->file('pdf_file')
                        ->store($folderPath, 'public');

                    Log::info("Files stored successfully.", [
                        'word' => $wordPath,
                        'pdf' => $pdfPath
                    ]);

                    $validation = $this->validateWordFormat(
                        Storage::disk('public')->path($wordPath),
                        $practicum
                    );

                    $isFormatValid = $validation['is_valid'];
                    $validationLogs = $validation['logs'];
                } else {

                    // TASK
                    $attachmentPath = $request->file('attachment_file')
                        ->store($folderPath, 'public');

                    Log::info("Attachment stored successfully.", [
                        'attachment' => $attachmentPath
                    ]);
                }

                // UPDATE STATUS REVISION
                if ($submission->status === 'revision') {
                    $submission->update([
                        'status' => 'pending'
                    ]);
                }

                // INSERT VERSION
                SubmissionVersion::create([
                    'submission_id'          => $submission->id,
                    'version_number'         => $versionNumber,
                    'word_file_path'         => $wordPath,
                    'pdf_file_path'          => $pdfPath,
                    'is_format_valid'        => $isFormatValid,
                    'system_validation_logs' => json_encode($validationLogs),
                    'attachment'             => $attachmentPath,
                    'assistant_notes'        => null,
                ]);

                Log::info("Submission version created successfully.");
            });

            // SUCCESS REDIRECT
            return redirect()->route('dashboard', [
                'practicum_id'  => $assignment->practicum_id,
                'assignment_id' => $assignment->id
            ])->with(
                'success',
                'Laporan berhasil dikumpulkan dan dianalisis oleh sistem.'
            );
        } catch (\Illuminate\Validation\ValidationException $ve) {

            Log::warning("Validation failed.", [
                'errors' => $ve->errors()
            ]);

            throw $ve;
        } catch (\Exception $e) {

            Log::error("FATAL ERROR IN SUBMISSION STORE FUNCTION!", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'upload_error' => $e->getMessage()
            ])->withInput();
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
                $reviewVersion = SubmissionVersion::findOrFail($id);

                if (!$reviewVersion) {
                    return response()->json(['message' => 'Versi berkas pengumpulan tidak ditemukan.'], 404);
                }
                // Ambil data versi dokumen paling baru yang diupload mahasiswa
                $submission = $reviewVersion->submission;


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
                $reviewVersion->update([
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
    /**
     * Core Engine pembedah file Word (.docx) - Versi Smart Extraction (Dominant Style)
     */
    private function validateWordFormat(string $filePath, $rules): array
    {
        $isValid = true;
        $logs = [];

        try {
            Log::info("PHPWord Smart Analysis Started.", ['path' => $filePath]);

            if (!file_exists($filePath)) {
                return ['is_valid' => false, 'logs' => ['error' => 'Berkas fisik laporan tidak ditemukan di server.']];
            }

            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            $sections = $phpWord->getSections();

            if (empty($sections)) {
                return ['is_valid' => false, 'logs' => ['error' => 'Dokumen kosong atau tidak terbaca.']];
            }

            // Aturan Standar
            $reqTop    = $rules->required_margin_top_cm ?? 4.0;
            $reqBottom = $rules->required_margin_bottom_cm ?? 3.0;
            $reqLeft   = $rules->required_margin_left_cm ?? 4.0;
            $reqRight  = $rules->required_margin_right_cm ?? 3.0;
            $reqFont   = $rules->required_font_name ?? 'Times New Roman';
            $reqSize   = $rules->required_font_size ?? 12;
            $reqSpace  = $rules->required_line_spacing ?? 1.5;

            // 1. Cek Margin (Hanya cek section pertama sebagai cover/body utama)
            $sectionStyle = $sections[0]->getStyle();
            $marginTop    = round($sectionStyle->getMarginTop() / 567, 1);
            $marginBottom = round($sectionStyle->getMarginBottom() / 567, 1);
            $marginLeft   = round($sectionStyle->getMarginLeft() / 567, 1);
            $marginRight  = round($sectionStyle->getMarginRight() / 567, 1);

            // Beri toleransi 0.1 cm untuk pembulatan Twips ke CM
            $marginTolerance = 0.1;

            if (abs($marginTop - $reqTop) > $marginTolerance) {
                $isValid = false;
                $logs['margin_top'] = "Margin atas {$marginTop} cm, seharusnya {$reqTop} cm.";
            }
            if (abs($marginBottom - $reqBottom) > $marginTolerance) {
                $isValid = false;
                $logs['margin_bottom'] = "Margin bawah {$marginBottom} cm, seharusnya {$reqBottom} cm.";
            }
            if (abs($marginLeft - $reqLeft) > $marginTolerance) {
                $isValid = false;
                $logs['margin_left'] = "Margin kiri {$marginLeft} cm, seharusnya {$reqLeft} cm.";
            }
            if (abs($marginRight - $reqRight) > $marginTolerance) {
                $isValid = false;
                $logs['margin_right'] = "Margin kanan {$marginRight} cm, seharusnya {$reqRight} cm.";
            }

            // --- SMART EXTRACTION ENGINE ---
            // Tally variables untuk mencari mayoritas style yang dipakai
            $fontNamesTally = [];
            $fontSizesTally = [];
            $spacingTally = [];
            $totalTextLength = 0;

            // 2. Deep Scanning Elemen
            foreach ($sections as $section) {
                foreach ($section->getElements() as $element) {

                    if (method_exists($element, 'getElements')) {
                        $paragraphTextLen = 0;
                        $currentSpacing = null;

                        // Ambil line spacing paragraf saat ini
                        if (method_exists($element, 'getParagraphStyle')) {
                            $pStyle = $element->getParagraphStyle();
                            if ($pStyle) {
                                $spacing = $pStyle->getSpacingLineRule();
                                $normalizedSpacing = 1.0;

                                if (is_numeric($spacing)) {
                                    if ($spacing == 360) $normalizedSpacing = 1.5;
                                    elseif ($spacing == 480) $normalizedSpacing = 2.0;
                                    elseif ($spacing == 240) $normalizedSpacing = 1.0;
                                    else $normalizedSpacing = round($spacing / 240, 1);
                                }
                                $currentSpacing = $normalizedSpacing;
                            }
                        }

                        // Ekstraksi Karakter
                        foreach ($element->getElements() as $textElement) {
                            if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                $text = trim($textElement->getText());
                                $textLen = strlen($text);

                                // SKIP baris kosong atau spasi doang
                                if ($textLen === 0) continue;

                                $paragraphTextLen += $textLen;
                                $totalTextLength += $textLen;

                                $fStyle = $textElement->getFontStyle();
                                if ($fStyle) {
                                    $fontName = $fStyle->getName() ?? 'Unknown';
                                    $fontSize = $fStyle->getSize() ?? 0;

                                    // Hitung kemunculan berdasarkan panjang karakter (pembobotan)
                                    $fontNamesTally[$fontName] = ($fontNamesTally[$fontName] ?? 0) + $textLen;
                                    $fontSizesTally[$fontSize] = ($fontSizesTally[$fontSize] ?? 0) + $textLen;
                                }
                            }
                        }

                        // Catat line spacing hanya jika paragraf tersebut memiliki teks valid
                        if ($paragraphTextLen > 0 && $currentSpacing !== null) {
                            $spacingTally[$currentSpacing] = ($spacingTally[$currentSpacing] ?? 0) + $paragraphTextLen;
                        }
                    }
                }
            }

            // 3. Evaluasi Berdasarkan Dominansi (Mayoritas)
            if ($totalTextLength > 0) {
                // Urutkan dari yang paling banyak dipakai ke paling sedikit
                arsort($fontNamesTally);
                arsort($fontSizesTally);
                arsort($spacingTally);

                $dominantFontName = array_key_first($fontNamesTally);
                $dominantFontSize = array_key_first($fontSizesTally);
                $dominantSpacing  = array_key_first($spacingTally);

                // Validasi font name mayoritas
                if ($dominantFontName && $dominantFontName !== $reqFont) {
                    $isValid = false;
                    $logs['font_name'] = "Mayoritas teks menggunakan font '{$dominantFontName}', wajib menggunakan '{$reqFont}'.";
                }

                // Validasi font size mayoritas
                if ($dominantFontSize && $dominantFontSize != $reqSize) {
                    $isValid = false;
                    $logs['font_size'] = "Mayoritas ukuran teks terdeteksi {$dominantFontSize}pt, standar wajib {$reqSize}pt.";
                }

                // Validasi line spacing mayoritas
                if ($dominantSpacing && $dominantSpacing != $reqSpace) {
                    $isValid = false;
                    $logs['line_spacing'] = "Mayoritas spasi baris terdeteksi {$dominantSpacing}, standar praktikum wajib {$reqSpace}.";
                }

                Log::info("Document Style Dominance:", [
                    'dominant_font' => $dominantFontName,
                    'dominant_size' => $dominantFontSize,
                    'dominant_spacing' => $dominantSpacing,
                    'total_analyzed_chars' => $totalTextLength
                ]);
            } else {
                $isValid = false;
                $logs['content'] = "Dokumen tampaknya hanya berisi gambar/lampiran tanpa teks laporan yang bisa dibaca sistem.";
            }
        } catch (\Exception $e) {
            Log::error("Error inside smart validateWordFormat engine.", ['msg' => $e->getMessage()]);
            $isValid = false;
            $logs['exception'] = "Gagal membedah format file: " . $e->getMessage();
        }

        return ['is_valid' => $isValid, 'logs' => $logs];
    }
}
