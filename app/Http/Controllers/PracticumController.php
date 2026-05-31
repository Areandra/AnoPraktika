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
            'name'                      => ['required', 'string', 'max:255'],
            'academic_year'             => ['required', 'string', 'max:255'],
            'required_margin_top_cm'    => ['required', 'numeric', 'between:0.5,10.0'],
            'required_margin_bottom_cm' => ['required', 'numeric', 'between:0.5,10.0'],
            'required_margin_left_cm'   => ['required', 'numeric', 'between:0.5,10.0'],
            'required_margin_right_cm'  => ['required', 'numeric', 'between:0.5,10.0'],
            'required_line_spacing'     => ['required', 'numeric', 'in:1.0,1.15,1.5,2.0'],
            'required_font_name'        => ['required', 'string', 'in:Times New Roman,Arial,Calibri,Helvetica'],
            'required_font_size'        => ['required', 'integer', 'between:8,24'],
        ]);


        $practicum = Practicum::create($validated);


        $practicum->users()->attach(Auth::id(), ['role' => 'assistant']);

        return redirect()->route('dashboard', ['practicum_id' => $practicum->id])
            ->with('success', "Kelas praktikum '{$practicum->name}' berhasil dibuat dengan konfigurasi format laporan kustom.");
    }

    public function join(Request $request)
    {
        $request->validate([
            'practicum_id' => ['required', 'exists:practicums,id']
        ]);

        $practicum = Practicum::findOrFail($request->practicum_id);
        $user = Auth::user();


        if ($practicum->users()->where('user_id', $user->id)->exists()) {
            return back()->withErrors([
                'practicum_id' => 'Anda sudah terdaftar atau bergabung di dalam kelas praktikum ini.'
            ]);
        }


        $practicum->users()->attach($user->id, ['role' => 'student']);

        return redirect()->route('dashboard', ['practicum_id' => $practicum->id])
            ->with('success', 'Anda berhasil bergabung ke dalam praktikum ' . $practicum->name . '.');
    }
}
