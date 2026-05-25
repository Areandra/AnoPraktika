<?php

namespace App\Http\Controllers;

use App\Models\Practicum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PracticumController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'academic_year' => 'required|string|max:255',
        ]);

        $practicum = Practicum::create($validated);
        $practicum->users()->attach(Auth::id(), ['role' => 'assistant']);

        // Menambahkan alert success
        return redirect()->route('dashboard', ['practicum_id' => $practicum->id])
            ->with('success', 'Kelas praktikum baru berhasil dibuat.');
    }

    public function join(Request $request)
    {
        $request->validate(['practicum_id' => 'required|exists:practicums,id']);

        $practicum = Practicum::findOrFail($request->practicum_id);
        $practicum->users()->attach(Auth::id(), ['role' => 'student']);

        // Menambahkan alert success
        return redirect()->route('dashboard', ['practicum_id' => $practicum->id])
            ->with('success', 'Anda berhasil bergabung ke dalam praktikum ' . $practicum->name . '.');
    }
}
