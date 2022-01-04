<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JsonResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->add([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);
        return $next($request);
    }
}
