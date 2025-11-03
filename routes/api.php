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

// Routes API version 1
Route::prefix('v1')->group(function () {

    /**
     * Routes d'authentification OAuth2
     */
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('verify', [AuthController::class, 'verify']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    });

    /**
     * Routes pour les comptes bancaires - PROTÉGÉES
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
    // Routes comptes accessibles à tous les utilisateurs authentifiés (utilisant Passport)
    Route::middleware(['auth:api'])->group(function () {
        Route::get('comptes', [AccountController::class, 'index']);
        Route::get('comptes/{id}', [AccountController::class, 'show']);

        // Routes comptes réservées aux administrateurs
        Route::middleware(['role:admin'])->group(function () {
            Route::post('comptes', [AccountController::class, 'store']);
            Route::patch('comptes/{id}', [AccountController::class, 'update']);
            Route::delete('comptes/{id}', [AccountController::class, 'destroy']);
            Route::post('comptes/{compteId}/bloquer', [AccountController::class, 'block']);
        });
    });

    // Routes de test sans authentification pour les tests
    Route::prefix('test')->group(function () {
        Route::post('comptes', [AccountController::class, 'store']);
        Route::get('comptes', [AccountController::class, 'index']);
        Route::get('comptes/{id}', [AccountController::class, 'show']);
        Route::patch('comptes/{id}', [AccountController::class, 'update']);
        Route::delete('comptes/{id}', [AccountController::class, 'destroy']);
    });

    // Route pour bloquer un compte
    Route::post('/comptes/{compteId}/bloquer', [AccountController::class, 'block']);

    // Route de test pour créer des comptes
    Route::post('/test/comptes', [AccountController::class, 'store']);

    // Route de test pour update
    Route::patch('/test-update/{id}', [AccountController::class, 'update']);

});

// Route temporaire pour tester la création de compte sans authentification
Route::prefix('v1')->middleware([
    RatingMiddleware::class,
    LoggingMiddleware::class
])->group(function () {
    Route::post('/test/comptes', [AccountController::class, 'store']);
});