<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\PasienAdded;


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

        // Simpan ke CSV
        $filePath = base_path('backend/models/data_pasien.csv');
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
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

        // Pancarkan event ketika pasien ditambahkan
        event(new PasienAdded($validatedData['nama'], $validatedData['gender'], $validatedData['umur']));

        // Redirect ke halaman rekomendasi obat dengan pesan sukses
        return redirect('/rekomendasi-obat')->with('success', 'Data pasien berhasil disimpan.');
    }
}
