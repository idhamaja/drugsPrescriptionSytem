<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PasienController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Ambil data dari API Flask
            $response = Http::get('http://127.0.0.1:5000/api/pasien');

            if ($response->successful()) {
                // Ambil data pasien dari respons API
                $data_pasien = $response->json();
            } else {
                // Jika API gagal, log error dan tampilkan data kosong atau error
                Log::error('Gagal mengambil data pasien dari API');
                $data_pasien = [];
            }
        } catch (\Exception $e) {
            Log::error('Exception saat mengambil data pasien: ' . $e->getMessage());
            $data_pasien = [];
        }

        // Paginasi manual
        $perPage = 10; // Jumlah data per halaman
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($data_pasien, ($currentPage - 1) * $perPage, $perPage);

        $paginatedData = new LengthAwarePaginator($currentItems, count($data_pasien), $perPage);
        $paginatedData->setPath($request->url());

        // Kirim data pasien yang dipaginasi ke view
        return view('rekomendasi-obat', ['data_pasien' => $paginatedData]);
    }

    // Fungsi untuk menyimpan data pasien
    public function simpanData(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'gender' => 'required|string',
            'umur' => 'required|integer|min:0',
            'diagnosa' => 'required|string|max:255',
            'resep_obat' => 'nullable|string',
        ]);

        try {
            // Simpan data pasien ke database
            $pasien = new Pasien();
            $pasien->nama = $validatedData['nama'];
            $pasien->gender = $validatedData['gender'];
            $pasien->umur = $validatedData['umur'];
            $pasien->diagnosa = $validatedData['diagnosa'];
            $pasien->resep_obat = $validatedData['resep_obat'];
            $pasien->save(); // Simpan ke database

            // Flash message jika berhasil
            return redirect()->back()->with('success', 'Diagnosa dan Resep Obat Berhasil Disimpan.');
        } catch (\Exception $e) {
            // Flash message jika terjadi error
            return redirect()->back()->with('error', 'Data Belum Tersimpan. Terjadi kesalahan.');
        }
    }


    // Metode baru untuk menampilkan hasil rekomendasi obat
    public function hasilRekomendasi($pasien_id)
    {
        // Ambil data pasien dengan paginasi (misalnya 10 data per halaman)
        $data_pasien = Pasien::paginate(10);

        // Kirim data pasien ke view
        return view('hasil-rekomendasi', ['data_pasien' => $data_pasien]);
    }
}
