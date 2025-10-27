<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié via Passport
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié. Token d\'accès requis.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        return $next($request);
    }
}
