<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Practicum extends Model
{
    protected $fillable = [
        'name',
        'academic_year',
        'required_margin_top_cm',
        'required_margin_bottom_cm',
        'required_margin_left_cm',
        'required_margin_right_cm',
        'required_line_spacing',
        'required_font_name',
        'required_font_size',
    ];

    // Relasi ke User (Mahasiswa & Asprak di dalam kelas ini)
    public function users()
    {
        return $this->belongsToMany(User::class, 'practicum_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    // Relasi ke daftar Modul/Tugas
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
