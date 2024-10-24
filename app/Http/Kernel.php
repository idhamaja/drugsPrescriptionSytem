<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel; // Ensure this is present and correct
use App\Http\Middleware\CheckForMaintenanceMode; // Add this line

class Kernel extends HttpKernel
{
    protected $middleware = [
        // Middleware global
        CheckForMaintenanceMode::class, // Updated to use the imported class
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
    ];

    // ... existing code ...
}
