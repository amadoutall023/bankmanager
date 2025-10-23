<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RatingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enregistrer les utilisateurs qui ont atteint le rate limit
        $response = $next($request);

        if ($response->getStatusCode() === 429) {
            // Log l'utilisateur qui a atteint le rate limit
            Log::warning('Rate limit atteint', [
                'user_id' => Auth::check() ? Auth::id() : null,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now(),
            ]);
        }

        return $response;
    }
}
