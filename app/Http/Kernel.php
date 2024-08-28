<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{

    protected $routeMiddleware = [
        // middleware lainnya
        'flaskapi' => \App\Http\Middleware\FlaskAPI::class,
    ];

    // ... existing code ...
}
