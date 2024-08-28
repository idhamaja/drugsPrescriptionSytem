<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosisResep extends Model
{
    use HasFactory;

    protected $fillable = ['diagnosis', 'resep_obat'];
}
