<?php

use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\TutorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LearnController::class, 'home'])->name('home');
Route::get('/belajar/{chapter}', [LearnController::class, 'chapter'])->name('chapter');
Route::get('/belajar/{chapter}/{lessonSlug}', [LearnController::class, 'lesson'])->name('lesson');

Route::get('/latihan', [PracticeController::class, 'index'])->name('practice');
Route::get('/latihan/ulang', [PracticeController::class, 'review'])->middleware('auth')->name('review');
Route::get('/kartu/{category?}', [PracticeController::class, 'cards'])->name('cards');

Route::get('/tes', [PageController::class, 'test'])->name('test');
Route::get('/ai', [PageController::class, 'ai'])->name('ai');
Route::get('/tentang', [PageController::class, 'about'])->name('about');

Route::get('/progres', [ProgressController::class, 'show'])->name('progress');
Route::middleware('auth')->group(function () {
    Route::get('/progres/export', [ProgressController::class, 'export'])->name('progress.export');
    Route::post('/progres/reset', [ProgressController::class, 'reset'])->name('progress.reset');
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

    // Endpoint JSON memakai sesi + CSRF yang sama dengan halaman web
    Route::prefix('api')->group(function () {
        Route::post('/attempts', [AttemptController::class, 'store'])->name('api.attempts');
        Route::post('/sync', [AttemptController::class, 'sync'])->name('api.sync');
        Route::post('/tutor', TutorController::class)->middleware('throttle:tutor')->name('api.tutor');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:8,1');
});
