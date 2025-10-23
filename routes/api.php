<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AccountController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API V1 Routes
Route::prefix('v1')->group(function () {
    /**
     * Lister tous les comptes
     *
     * GET /api/v1/comptes
     *
     * Query Parameters:
     * - page: Numéro de page (default: 1)
     * - limit: Nombre d'éléments par page (default: 10, max: 100)
     * - type: Filtrer par type (epargne, cheque)
     * - statut: Filtrer par statut (active, inactive, closed)
     * - search: Recherche par titulaire ou numéro
     * - sort: Tri (created_at, balance, account_number)
     * - order: Ordre (asc, desc)
     *
     * Headers:
     * - Authorization: Bearer {token}
     * - Accept: application/json
     *
     * Response:
     * {
     *   "success": true,
     *   "data": [...],
     *   "pagination": {...},
     *   "links": {...}
     * }
     */
    Route::get('/comptes', [AccountController::class, 'index']);
});