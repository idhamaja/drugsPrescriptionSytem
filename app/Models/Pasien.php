<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    protected $table = 'pasiens';
    protected $fillable = ['nama', 'gender', 'umur', 'diagnosa', 'resep_obat'];
}
