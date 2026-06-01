<?php

namespace App\Http\Middleware;

use App\Models\Assignment;
use App\Models\SubmissionVersion; // Tambahkan import ini di atas
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PractikumMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Loloskan jika di dashboard dan tidak sedang memilih praktikum
        if ($request->routeIs('dashboard') && !$request->has('practicum_id')) {
            return $next($request);
        }

        // 2. Ambil practicum_id langsung dari request atau parameter route
        $practicumId = $request->input('practicum_id') ?? $request->route('practicum_id');

        // 3. Jika tidak ada, coba cari lewat assignment_id
        if (!$practicumId) {
            $assignmentParam = $request->input('assignmentId') ?? $request->route('assignment') ?? $request->route('assignment_id');
            $assignmentId = is_object($assignmentParam) ? $assignmentParam->id : $assignmentParam;

            if ($assignmentId) {
                $practicumId = Assignment::query()->find($assignmentId)?->practicum_id;
            }
        }

        // 4. BARU: Jika masih tidak ada, coba cari lewat submissionVersion (untuk route review)
        if (!$practicumId) {
            $versionParam = $request->route('submissionVersion') ?? $request->route('submission_version');
            $versionId = is_object($versionParam) ? $versionParam->id : $versionParam;

            if ($versionId) {
                // Menelusuri relasi: Versi -> Submission -> Assignment -> Practicum ID
                // Menggunakan eager loading (with) agar hemat query ke database
                $practicumId = SubmissionVersion::query()->with('submission.assignment')
                    ->find($versionId)
                    ?->submission
                    ?->assignment
                    ?->practicum_id;
            }
        }

        // 5. Jika ID praktikum tetap tidak ditemukan, tendang balik ke dashboard
        if (!$practicumId) {
            return redirect()->route('dashboard')->withErrors([
                'practicum_id' => 'ID praktikum tidak ditemukan untuk melangsungkan aksi ini.'
            ]);
        }

        // 6. Cek apakah user (asprak/mahasiswa) terdaftar di praktikum ini dengan status 'joined'
        $isJoined = Auth::user()->practicums()
            ->wherePivot('status', 'joined')
            ->where('practicums.id', $practicumId)
            ->exists();

        $isRequestingJoin = Auth::user()->practicums()
            ->wherePivot('status', 'request')
            ->where('practicums.id', $practicumId)
            ->exists();

        if (!$isJoined) {
            return redirect()->route('dashboard')->withErrors(['error' => $isRequestingJoin
                ? 'Anda belum di setujui untuk bergabung, menunggu persetujuan Asprak.'
                : 'Anda tidak memiliki akses ke praktikum ini.']);
        }

        return $next($request);
    }
}
