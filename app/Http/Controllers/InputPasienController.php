<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InputPasienController extends Controller
{
    public function showForm()
    {
        return view('input_pasien');
    }

    public function storeData(Request $request)
    {
        // Add your data storage logic here
    }
}
