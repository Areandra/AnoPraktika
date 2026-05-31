<?php

namespace App\Http\Controllers;

use App\Models\Practicum;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'practicum_id' => 'required|exists:practicums,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:module,task',
            'deadline'     => 'required|date',
        ]);

        $assignment = \App\Models\Assignment::create($validated);


        return redirect()->route('dashboard', [
            'practicum_id'  => $request->practicum_id,
            'assignment_id' => $assignment->id
        ])->with('success', 'Tugas/Modul baru berhasil ditambahkan ke dalam praktikum.');
    }
}
