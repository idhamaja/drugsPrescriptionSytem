<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RekomendasiController extends Controller
{
    // Autocomplete untuk diagnosa
    public function getDiagnosa(Request $request)
    {
        // Mengirim request GET ke API Flask
        $response = Http::get('http://127.0.0.1:5000/api/diagnosis', [
            'q' => $request->input('q')  // Mengambil query diagnosis dari request
        ]);

        // Cek apakah respons dari API berhasil
        if ($response->successful()) {
            return $response->json();  // Mengembalikan hasil diagnosis
        }

        // Mengembalikan pesan error jika gagal
        return response()->json(['error' => 'Tidak bisa mengambil diagnosis dari API Flask'], 500);
    }

    // Mengambil rekomendasi obat berdasarkan diagnosis yang dipilih
    public function getRekomendasiObat(Request $request)
    {
        // Mengirim request POST ke API Flask
        $response = Http::post('http://127.0.0.1:5000/recommend', [
            'diagnosis' => $request->input('diagnosis')  // Mengambil diagnosis dari request
        ]);

        // Cek apakah respons dari API berhasil
        if ($response->successful()) {
            return $response->json();  // Mengembalikan hasil rekomendasi obat
        }

        // Mengembalikan pesan error jika gagal
        return response()->json(['error' => 'Tidak ada rekomendasi obat untuk diagnosis ini'], 500);
    }

    //
    public function contentBasedFiltering(Request $request)
    {
        // Ambil diagnosis dari request
        $diagnosis = $request->input('diagnosis');

        // Validasi apakah diagnosis ada
        if (!$diagnosis) {
            return response()->json(['error' => 'Diagnosis input is required'], 400);
        }

        // Kirim request ke Flask API untuk content-based filtering
        try {
            $response = Http::post('http://127.0.0.1:5000/api/cbf', [
                'diagnosis' => $diagnosis
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['error' => 'Error in fetching data from Flask'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
