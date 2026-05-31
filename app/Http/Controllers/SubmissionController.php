<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; 
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\Process\Process;

class SubmissionController extends Controller
{
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

                
                if ($submission->status === 'approved') {
                    throw new \Exception('Laporan telah di-ACC, tidak dapat mengunggah revisi.');
                }

                
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
                        $practicum,
                        Storage::disk('public')->path($pdfPath)
                    );

                    $isFormatValid = $validation['is_valid'];
                    $validationLogs = $validation['logs'];
                } else {

                    
                    $attachmentPath = $request->file('attachment_file')
                        ->store($folderPath, 'public');

                    Log::info("Attachment stored successfully.", [
                        'attachment' => $attachmentPath
                    ]);
                }

                
                if ($submission->status === 'revision') {
                    $submission->update([
                        'status' => 'pending'
                    ]);
                }

                
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

    public function review(Request $request, $id)
    {
        
        Log::info('Before update', [
            'notes' => $request->assistant_notes,
            'coords' => $request->annotation_coordinates
        ]);

        $request->validate([
            'status'                 => ['required', 'in:approved,revision'],
            'assistant_notes'        => ['required', 'string'],
            'annotation_coordinates' => ['nullable', 'string'], 
        ]);




        try {
            
            return DB::transaction(function () use ($request, $id) {

                
                $reviewVersion = SubmissionVersion::findOrFail($id);

                if (!$reviewVersion) {
                    return response()->json(['message' => 'Versi berkas pengumpulan tidak ditemukan.'], 404);
                }
                
                $submission = $reviewVersion->submission;


                
                $submission->update([
                    'status'                => $request->status,
                    'assigned_assistant_id' => Auth::id(), 
                ]);

                
                $rawCoordinates = $request->input('annotation_coordinates');
                
                $decoded = json_decode($rawCoordinates, true);
                $jsonToSave = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                    ? $rawCoordinates
                    : json_encode([]);

                
                $reviewVersion->update([
                    'assistant_notes' => $request->assistant_notes,
                    
                    'annotation_coordinates' => $jsonToSave
                ]);

                
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

    private function validateWordFormat(string $filePath, $rules, string $pdfPath): array
    {
        $scriptPath = base_path('app/Scripts/validate-doc.py');

        
        $pythonVenvPath = base_path('venv/bin/python3');

        
        $process = new Process([$pythonVenvPath, $scriptPath, $filePath, $pdfPath]);
        try {
            $process->setTimeout(90);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("DDEV Python Venv Execution Failed: " . $process->getErrorOutput());
                return [
                    'is_valid' => false,
                    'logs' => ['error' => 'Gagal menjalankan mesin validasi dokumen di server.']
                ];
            }

            $result = json_decode($process->getOutput(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Python output json corrupt: " . $process->getOutput());
                return [
                    'is_valid' => false,
                    'logs' => ['error' => 'Format laporan hasil analisis tidak valid.']
                ];
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Error bridge Laravel-Python: " . $e->getMessage());
            return [
                'is_valid' => false,
                'logs' => ['error' => 'Terjadi kendala pada sistem pembacaan file.']
            ];
        }
    }
}
