<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InputPasienController extends Controller
{
    // Menampilkan form input pasien
    public function showForm()
    {
        return view('input_pasien');  // Menampilkan form input pasien
    }

    // Menyimpan data pasien ke file CSV
    public function storeData(Request $request)
    {
        // Validasi data input dari form
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'gender' => 'required|string',
            'umur' => 'required|integer|min:0',
        ]);

        // Normalisasi input data
        $inputNama = strtolower(trim(preg_replace('/\s+/', ' ', str_replace('"', '', $validatedData['nama']))));
        $inputGender = strtolower(trim(str_replace('"', '', $validatedData['gender'])));
        $inputUmur = strval($validatedData['umur']);

        // Kirim data ke API backend
        try {
            $response = Http::post('http://127.0.0.1:5000/api/add_pasien', [
                'Nama' => $validatedData['nama'],
                'Gender' => $validatedData['gender'],
                'Umur' => $validatedData['umur'],
            ]);

            if ($response->status() == 409) {
                return redirect('/rekomendasi-obat')->with('error', 'Data pasien sudah ada.');
            } elseif (!$response->successful()) {
                return redirect('/rekomendasi-obat')->with('error', 'Gagal menyimpan data pasien.');
            }
        } catch (\Exception $e) {
            Log::error("Error sending data to Flask: " . $e->getMessage());
            return redirect('/rekomendasi-obat')->with('error', 'Terjadi kesalahan saat menyimpan data pasien.');
        }

        // Jika data berhasil disimpan, simpan juga ke CSV lokal di Laravel
        $filePath = base_path('backend/models/data_pasien.csv');
        $file = fopen($filePath, 'a');
        if (filesize($filePath) == 0) {
            fputcsv($file, ['Nama', 'Gender', 'Umur']);
        }
        fputcsv($file, [
            $validatedData['nama'],
            $validatedData['gender'],
            $validatedData['umur']
        ]);
        fclose($file);

        // Redirect ke halaman rekomendasi obat dengan pesan sukses
        return redirect('/rekomendasi-obat')->with('success', 'Data pasien berhasil disimpan.');
    }
}
