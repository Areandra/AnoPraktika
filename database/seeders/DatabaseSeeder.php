<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $student = User::firstOrCreate(['email' => 'student@example.com'], [
            'name' => 'Areandra (Student)',
            'password' => bcrypt('password'),
            'identifier' => '1234567890',
        ]);

        $assistant = User::firstOrCreate(['email' => 'assistant@example.com'], [
            'name' => 'Budi (Assistant)',
            'password' => bcrypt('password'),
            'identifier' => '0987654321',
        ]);

        // 2. Practicums
        $practicum = \App\Models\Practicum::firstOrCreate(['name' => 'Algoritma dan Pemrograman'], [
            'academic_year' => '2025/2026',
        ]);

        $practicum2 = \App\Models\Practicum::firstOrCreate(['name' => 'Struktur Data'], [
            'academic_year' => '2025/2026',
        ]);

        // 3. PracticumUsers
        \App\Models\PracticumUser::firstOrCreate([
            'practicum_id' => $practicum->id,
            'user_id' => $student->id,
        ], ['role' => 'student']);

        \App\Models\PracticumUser::firstOrCreate([
            'practicum_id' => $practicum->id,
            'user_id' => $assistant->id,
        ], ['role' => 'assistant']);

        \App\Models\PracticumUser::firstOrCreate([
            'practicum_id' => $practicum2->id,
            'user_id' => $student->id,
        ], ['role' => 'student']);

        // 4. Assignments
        \App\Models\Assignment::firstOrCreate([
            'practicum_id' => $practicum->id,
            'title' => 'Modul 1: Pengenalan C++',
        ], [
            'description' => 'Mempelajari sintaks dasar C++, input/output, dan tipe data.',
            'type' => 'module',
            'deadline' => now()->addDays(7),
        ]);

        \App\Models\Assignment::firstOrCreate([
            'practicum_id' => $practicum->id,
            'title' => 'Tugas 1: Kalkulator Sederhana',
        ], [
            'description' => 'Buat program kalkulator menggunakan switch-case di C++.',
            'type' => 'task',
            'deadline' => now()->addDays(14),
        ]);
    }
}
