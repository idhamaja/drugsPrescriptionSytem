<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiagnosisResep;
use App\Rules\DiagnosisExists;
use Illuminate\Support\Facades\Log;



class DiagnosisController extends Controller
{


    public function rules()
    {
        return [
            'diagnosis' => ['required', new DiagnosisExists],
            'resep_obat' => ['required'],  // Validasi lainnya
        ];
    }
    public function store(Request $request)
    {
        try {
            Log::info('Data yang diterima:', $request->all());  // Log data yang diterima

            foreach ($request->input('data') as $item) {
                DiagnosisResep::create([
                    'diagnosis' => $item['diagnosis'],
                    'resep_obat' => $item['resep_obat'],
                ]);
            }

            return response()->json(['success' => 'Data berhasil disimpan']);
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan data: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }
}
