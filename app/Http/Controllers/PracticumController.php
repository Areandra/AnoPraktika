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
            'name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:255',
        ]);

        // Buat praktikum dengan default format parameter bawaan DB
        $practicum = Practicum::create($validated);

        // Otomatis daftarkan pembuatnya sebagai 'assistant' di tabel pivot
        $practicum->users()->attach(Auth::id(), ['role' => 'assistant']);

        return redirect()->route('dashboard', ['practicum_id' => $practicum->id]);
    }

    public function join(Request $request)
    {
        $request->validate(['practicum_id' => 'required|exists:practicums,id']);

        $practicum = Practicum::findOrFail($request->practicum_id);

        // Daftarkan diri sebagai 'student'
        $practicum->users()->attach(Auth::id(), ['role' => 'student']);

        return redirect()->route('dashboard', ['practicum_id' => $practicum->id]);
    }
}
