<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class InputPasienController extends Controller{
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

        // Tentukan path ke file CSV (pastikan folder 'backend/models' berada di root direktori proyek)
        $filePath = base_path('backend/models/data_pasien.csv');

        // Pastikan folder untuk CSV sudah ada
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);  // Buat folder jika belum ada
        }

        // Buka file CSV dengan mode append (menambah data di akhir file)
        $file = fopen($filePath, 'a');

        // Cek apakah file CSV sudah ada, jika belum tambahkan header
        $fileExists = file_exists($filePath);
        if (!$fileExists || filesize($filePath) == 0) {
            fputcsv($file, ['Nama', 'Gender', 'Umur']);  // Tambahkan header jika file baru dibuat
        }

        // Simpan data pasien ke CSV
        fputcsv($file, [
            $validatedData['nama'],
            $validatedData['gender'],
            $validatedData['umur']
        ]);

        // Tutup file CSV
        fclose($file);

        // Redirect ke halaman rekomendasi obat dengan flash message
        return redirect('/rekomendasi-obat')->with('success', 'Data pasien berhasil disimpan.');
    }
}
