<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PracticumController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

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

    Route::middleware('practicum')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::patch('/practicums/{practicum_id}/accept/{user_id}', [PracticumController::class, 'accept'])->name('practicum.accept');
        Route::delete('/practicums/{practicum_id}/kick/{user_id}', [PracticumController::class, 'kick'])->name('practicum.kick');

        Route::post('/assignments/create', [AssignmentController::class, 'store'])->name('assignments.store');

        Route::post('/submissions/{assignment}/store', [SubmissionController::class, 'store'])->name('submissions.store');

        Route::post('/submissions/{submissionVersion}/review', [SubmissionController::class, 'review'])->name('submissions.review');
    });

    Route::post('/practicums/create', [PracticumController::class, 'store'])->name('practicums.store');
    Route::post('/practicums/join', [PracticumController::class, 'join'])->name('practicums.join');
});
