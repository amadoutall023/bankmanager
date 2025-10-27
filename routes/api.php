<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Middleware\RatingMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\LoggingMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route pour servir la documentation Swagger JSON
Route::get('/docs-json', function () {
    $path = storage_path('api-docs/api-docs.json');
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin'
        ]);
    }
    return response()->json(['error' => 'Documentation not found'], 404);
});

// Routes API version 1 (simplifiées sans authentification)
Route::prefix('v1')->middleware([
    RatingMiddleware::class,
    LoggingMiddleware::class
])->group(function () {
    // Middleware CORS pour toutes les routes API
    Route::middleware(function ($request, $next) {
        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        return $response;
    });

    /**
     * Routes pour les comptes bancaires
     *
     * GET /api/v1/comptes - Lister tous les comptes (Admin: tous, Client: les siens)
     * POST /api/v1/comptes - Créer un compte (Admin seulement)
     * GET /api/v1/comptes/{id} - Afficher un compte spécifique
     * PATCH /api/v1/comptes/{id} - Modifier un compte (Admin seulement)
     * DELETE /api/v1/comptes/{id} - Supprimer un compte (Admin seulement)
     *
     * Query Parameters pour GET /api/v1/comptes:
     * - page: numéro de page (défaut: 1)
     * - limit: éléments par page (défaut: 10, max: 100)
     * - type: filtrer par type (epargne, cheque)
     * - statut: filtrer par statut (actif, bloque, ferme)
     * - search: recherche par titulaire ou numéro
     * - sort: tri (dateCreation, solde, titulaire)
     * - order: ordre (asc, desc)
     */
    // Toutes les routes comptes sans restriction
    Route::apiResource('comptes', AccountController::class);

    // Route pour bloquer un compte
    Route::post('/comptes/{compteId}/bloquer', [AccountController::class, 'block']);

    // Route de test pour créer des comptes
    Route::post('/test/comptes', [AccountController::class, 'store']);

});

// Route temporaire pour tester la création de compte sans authentification
Route::prefix('v1')->middleware([
    RatingMiddleware::class,
    LoggingMiddleware::class
])->group(function () {
    Route::post('/test/comptes', [AccountController::class, 'store']);
});