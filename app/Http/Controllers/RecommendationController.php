<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;

class RecommendationController extends Controller
{
    public function show($id)
    {
        // Logika untuk menampilkan rekomendasi obat berdasarkan ID pasien
        $patient = Patient::find($id);
        if (!$patient) {
            return redirect('/input-pasien')->with('error', 'Pasien tidak ditemukan');
        }

        $rekomendasi_obat = []; // Panggil fungsi atau service untuk rekomendasi obat

        return view('rekomendasi-obat', compact('patient', 'rekomendasi_obat'));
    }
}
