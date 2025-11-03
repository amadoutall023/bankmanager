<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token d\'accès manquant',
                'error' => 'missing_token'
            ], 401);
        }

        $decoded = $this->jwtService->decodeToken($token);

        if (!$decoded) {
            return response()->json([
                'success' => false,
                'message' => 'Token d\'accès invalide',
                'error' => 'invalid_token'
            ], 401);
        }

        if ($decoded->exp < time()) {
            return response()->json([
                'success' => false,
                'message' => 'Token d\'accès expiré',
                'error' => 'expired_token'
            ], 401);
        }

        // Ajouter l'utilisateur au request pour un accès facile
        $request->merge(['jwt_user' => json_decode(json_encode($decoded), true)]);

        return $next($request);
    }
}