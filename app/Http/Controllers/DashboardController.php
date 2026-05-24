<?php

namespace App\Http\Controllers;

use App\Models\Practicum;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. DATA SIDEBAR: Ambil semua praktikum yang diikuti oleh user saat ini
        $myPracticums = $user->practicums()->withPivot('role')->get();

        // Data untuk Modal Join: Ambil praktikum yang BELUM diikuti oleh user
        $availablePracticums = Practicum::whereDoesntHave('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->get();

        // Ambil Praktikum & Assignment yang sedang aktif di-klik dari URL
        $activePracticum = null;
        $activeAssignment = null;
        $roleInActivePracticum = null;

        $viewMode = 'empty'; // default tampilan tengah kosong
        $centerData = [];

        if ($request->has('practicum_id')) {
            $activePracticum = $user->practicums()
                ->withCount(['users as total_students' => function ($q) {
                    $q->where('role', 'student');
                }])
                ->withCount(['assignments as total_modules' => function ($q) {
                    $q->where('type', 'module');
                }])
                ->withCount(['assignments as total_tasks' => function ($q) {
                    $q->where('type', 'task');
                }])
                ->findOrFail($request->practicum_id);

            $roleInActivePracticum = $activePracticum->pivot->role; // 'student' atau 'assistant'
            $viewMode = 'practicum_selected';

            // Jika ada tugas/modul yang di-klik di panel kanan
            if ($request->has('assignment_id')) {
                $activeAssignment = Assignment::query()->where('practicum_id', $activePracticum->id)
                    ->findOrFail($request->assignment_id);

                if ($request->get('show_users') == 'true') {
                    // Mode Tampilan: List User dalam Praktikum
                    $viewMode = 'list_users';
                    $centerData['users'] = $activePracticum->users()->withPivot('role')->get();
                } elseif ($roleInActivePracticum === 'assistant') {
                    // Mode Assistant: Tampilkan semua submission masuk pada tugas tersebut
                    $viewMode = 'assistant_assignment_review';
                    $centerData['submissions'] = Submission::with(['student', 'latestVersion'])
                        ->where('assignment_id', $activeAssignment->id)
                        ->get();
                } else {
                    // Mode Student: Tampilkan Detail deskripsi tugas & riwayat submission miliknya sendiri
                    $viewMode = 'student_assignment_view';
                    $centerData['my_submission'] = Submission::with('versions')
                        ->where('assignment_id', $activeAssignment->id)
                        ->where('student_id', $user->id)
                        ->first();
                }
            }
        }

        return view('workspace.index', compact(
            'myPracticums',
            'availablePracticums',
            'activePracticum',
            'activeAssignment',
            'roleInActivePracticum',
            'viewMode',
            'centerData'
        ));
    }
}
