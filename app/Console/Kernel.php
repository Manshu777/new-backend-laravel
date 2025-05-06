<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [

    ];

    protected $middlewareGroups = [
        'web' => [
       
        ],

        'api' => [
            'throttle:api',
            'bindings',
        ],
    ];

    protected $routeMiddleware = [

       
        'ensure.token' => \App\Http\Middleware\EnsureTokenIsValid::class, // Add custom middleware here
    ];
}
