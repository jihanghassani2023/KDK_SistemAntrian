<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        // middleware bawaan Laravel
        'auth' => \App\Http\Middleware\Authenticate::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // middleware buatanmu
        'role' => \App\Http\Middleware\CheckRole::class, // <-- PASTIKAN ADA INI
    ];

}
