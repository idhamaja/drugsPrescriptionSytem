<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengelompokanController extends Controller
{
    // Fungsi untuk menampilkan halaman hasil pengelompokan
    public function index()
    {
        return view('hasil-pengelompokan'); // Pastikan view ini ada di resources/views
    }
}
