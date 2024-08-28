<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;

class PatientController extends Controller
{
    public function create()
    {
        return view('input-pasien');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string',
            'umur' => 'required|integer|min:0',
        ]);

       // Simpan data pasien
    $patient = Patient::create($request->only(['nama', 'jenis_kelamin', 'umur']));

    // Redirect ke halaman rekomendasi obat dengan ID pasien
    return redirect()->route('rekomendasi-obat', ['id' => $patient->id]);
    }

}

