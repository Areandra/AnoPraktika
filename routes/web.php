<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PracticumController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Route::get('/', function () {
//     return view('welcome');
// });

Route::prefix('login')->group(function () {
    Route::view('', 'auth.login')->name('login');
    Route::post('', [AuthController::class, 'login'])->name('login');
});

Route::prefix('register')->group(function () {
    Route::view('', 'auth.register')->name('register');
    Route::post('', [AuthController::class, 'register'])->name('register');
});


Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Hub Pusat Aplikasi (Sesuai Layout Desain Komponen)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD Praktikum (Join & Create)
    Route::post('/practicums/create', [PracticumController::class, 'store'])->name('practicums.store');
    Route::post('/practicums/join', [PracticumController::class, 'join'])->name('practicums.join');

    // CRUD Assignment
    Route::post('/assignments/create', [AssignmentController::class, 'store'])->name('assignments.store');

    // CRUD Submissions (Upload & Review)
    Route::post('/submissions/{assignment}/store', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::post('/submissions/{submissionVersion}/review', [SubmissionController::class, 'review'])->name('submissions.review');
});
