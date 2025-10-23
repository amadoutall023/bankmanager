<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Middleware\RatingMiddleware;

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
        return response()->file($path, ['Content-Type' => 'application/json']);
    }
    return response()->json(['error' => 'Documentation not found'], 404);
});

// Routes API version 1
Route::prefix('v1')->middleware([RatingMiddleware::class])->group(function () {

    /**
     * Routes pour les comptes bancaires
     *
     * GET /api/v1/comptes - Lister tous les comptes (Admin: tous, Client: les siens)
     * POST /api/v1/comptes - Créer un compte (Admin seulement)
     * GET /api/v1/comptes/{id} - Afficher un compte spécifique
     * PUT /api/v1/comptes/{id} - Modifier un compte (Admin seulement)
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
    Route::apiResource('comptes', AccountController::class);

});