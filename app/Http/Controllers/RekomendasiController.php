<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RekomendasiController extends Controller
{
    // Autocomplete untuk diagnosa
    public function getDiagnosa(Request $request)
    {
        $response = Http::get('http://127.0.0.1:5000/api/diagnosis', [
            'q' => $request->input('q')
        ]);

        if ($response->successful()) {
            return $response->json();  // Mengembalikan hasil autocomplete
        }

        return response()->json(['error' => 'Tidak bisa mengambil diagnosis dari API Flask'], 500);
    }

    // Mengambil rekomendasi obat berdasarkan diagnosis yang dipilih
    public function getRekomendasiObat(Request $request)
    {
        $response = Http::post(url: 'http://127.0.0.1:5000/recommend', data: [
            'diagnosis' => $request->input(key: 'diagnosis')
        ]);

        if ($response->successful()) {
            return $response->json();  // Mengembalikan hasil rekomendasi obat
        }

        return response()->json(['error' => 'Tidak ada rekomendasi obat untuk diagnosis ini'], 500);
    }
}
