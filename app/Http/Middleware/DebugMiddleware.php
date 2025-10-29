<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Log pour voir si le middleware est appelé
        \Log::info('DebugMiddleware: ' . $request->method() . ' ' . $request->path());
        
        return $next($request);
    }
}