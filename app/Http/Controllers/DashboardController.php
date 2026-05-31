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


        $myPracticums = $user->practicums()->withPivot('role')->get();


        $availablePracticums = Practicum::whereDoesntHave('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->get();


        $activePracticum = null;
        $activeAssignment = null;
        $roleInActivePracticum = null;

        $viewMode = 'empty';
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

            $roleInActivePracticum = $activePracticum->pivot->role;
            $viewMode = 'practicum_selected';


            if ($request->has('assignment_id')) {
                $activeAssignment = Assignment::query()->where('practicum_id', $activePracticum->id)
                    ->findOrFail($request->assignment_id);

                if ($request->get('show_users') == 'true') {

                    $viewMode = 'list_users';
                    $centerData['users'] = $activePracticum->users()->withPivot('role')->get();
                } elseif ($roleInActivePracticum === 'assistant') {

                    $viewMode = 'assistant_assignment_review';
                    $centerData['submissions'] = Submission::with(['student', 'versions'])
                        ->where('assignment_id', $activeAssignment->id)
                        ->get();
                } else {

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
