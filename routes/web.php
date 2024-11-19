<?php

use App\Http\Controllers\InputPasienController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\RekomendasiController;
use App\Http\Controllers\PengelompokanController;

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


