<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    // public function submit(Request $request)
    // {
    //     $diagnose = $request->input('diagnose');
    //     $gender = $request->input('gender');
    //     $age = $request->input('age');

    //     // Process the data as needed

    //     return redirect()->back()->with('success', 'Prescription recommendation generated successfully.');
    // }

    public function result() {
        // Your logic here
        return view('result');
       }
}


