<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosisController;

Route::get('/input-pasien', [PatientController::class, 'create']);
Route::post('/input-pasien', [PatientController::class, 'store']);
Route::get('/rekomendasi-obat', [RecommendationController::class, 'show']);
Route::get('/rekomendasi-obat/{id}', [RecommendationController::class, 'show'])->name('rekomendasi-obat');

Route::post('/save-diagnosis-resep', [DiagnosisController::class, 'store'])->name('save-diagnosis-resep');
