<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB; // Add this line

class DiagnosisExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Periksa apakah diagnosis ada dalam database
        return DB::table('diagnosis')->where('name', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Diagnosis yang Anda masukkan tidak ada dalam data kami.';
    }
}

