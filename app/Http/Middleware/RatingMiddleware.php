<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RatingMiddleware
{
    /**
     * Handle an incoming request.
     * Enregistre les utilisateurs qui atteignent 10 requêtes par jour
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $userId = $user->id;
            $today = now()->toDateString();
            $cacheKey = "user_requests_{$userId}_{$today}";

            // Incrémenter le compteur de requêtes pour aujourd'hui
            $requestCount = Cache::increment($cacheKey);

            // Si c'est la première requête du jour, définir une expiration
            if ($requestCount === 1) {
                Cache::put($cacheKey, 1, now()->endOfDay());
            }

            // Logger si l'utilisateur atteint 10 requêtes par jour
            if ($requestCount >= 10) {
                Log::info("Utilisateur {$user->email} (ID: {$userId}) a atteint {$requestCount} requêtes aujourd'hui", [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'request_count' => $requestCount,
                    'date' => $today,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'route' => $request->route() ? $request->route()->getName() : 'unknown'
                ]);
            }
        }

        return $next($request);
    }
}
