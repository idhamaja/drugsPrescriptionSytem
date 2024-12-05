<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\PasienController;
use App\Http\Controllers\RekomendasiController;
use App\Http\Controllers\PengelompokanController;
use App\Http\Controllers\InputPasienController;
use App\Http\Controllers\Auth\LogoutController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/rekomendasi-obat', [PasienController::class, 'index'])->middleware(['auth'])->name('rekomendasi.obat');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/pasien', [PasienController::class, 'index']);
    Route::get('/rekomendasi-obat', [PasienController::class, 'index']);

    Route::get('/api/diagnosis', [RekomendasiController::class, 'getDiagnosa']);
    Route::post('/api/rekomendasi-obat', [RekomendasiController::class, 'getRekomendasiObat']);

    Route::get('/input-pasien', [InputPasienController::class, 'showForm']);  // Display the input form
    Route::post('/input-pasien', [InputPasienController::class, 'storeData']);  // Handle form submission and save data


    Route::post('/simpan-data', [PasienController::class, 'simpanData']);
    Route::get('/hasil-rekomendasi/{pasien_id}', [PasienController::class, 'hasilRekomendasi'])->name('hasil.rekomendasi');

    Route::get('/hasil-pengelompokan', [PengelompokanController::class, 'index']);

    Route::post('/api/cbf', [RekomendasiController::class, 'contentBasedFiltering']);
});

Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

require __DIR__ . '/auth.php';
