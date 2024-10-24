<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasiens'; // Pastikan tabel sesuai dengan tabel database
    protected $fillable = ['nama', 'gender', 'umur', 'diagnosa', 'resep_obat']; // Sesuaikan field dengan tabel
}
