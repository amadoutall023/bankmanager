<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountCollection;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Traits\ApiResponseTrait;

class AccountController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lister tous les comptes (Admin) ou les comptes du client connecté
     * GET /api/v1/comptes
     */
    public function index(Request $request)
    {
        // Pour cette version simplifiée, on retourne tous les comptes sans authentification
        $query = Account::with('client.user')->notDeleted();

        // Filtres
        if ($request->has('type') && in_array($request->type, ['epargne', 'cheque'])) {
            $query->where('type', $request->type);
        }

        if ($request->has('statut')) {
            $statusMap = [
                'actif' => 'active',
                'bloque' => 'inactive',
                'ferme' => 'closed'
            ];
            if (array_key_exists($request->statut, $statusMap)) {
                $query->where('status', $statusMap[$request->statut]);
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhereHas('client.user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortMap = [
            'dateCreation' => 'created_at',
            'solde' => 'balance',
            'titulaire' => 'client.user.name'
        ];

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        if (array_key_exists($sort, $sortMap)) {
            $sortField = $sortMap[$sort];
            if ($sortField === 'client.user.name') {
                $query->join('clients', 'accounts.client_id', '=', 'clients.id')
                      ->join('users', 'clients.user_id', '=', 'users.id')
                      ->orderBy('users.name', $order)
                      ->select('accounts.*');
            } else {
                $query->orderBy($sortField, $order);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min($request->get('limit', 10), 100);
        $accounts = $query->paginate($perPage);

        return $this->successResponse(
            new AccountCollection($accounts),
            'Liste des comptes récupérée avec succès'
        );
    }

    /**
     * Créer un nouveau compte
     * POST /api/v1/comptes
     */
    public function store(StoreAccountRequest $request)
    {
        $validated = $request->validated();

        $account = Account::create($validated);

        return $this->successResponse(
            new AccountResource($account),
            'Compte créé avec succès',
            201
        );
    }

    /**
     * Afficher un compte spécifique
     * GET /api/v1/comptes/{id}
     */
    public function show(string $id)
    {
        $account = Account::with('client.user')->notDeleted()->findOrFail($id);

        return $this->successResponse(
            new AccountResource($account),
            'Compte récupéré avec succès'
        );
    }

    /**
     * Mettre à jour un compte
     * PUT /api/v1/comptes/{id}
     */
    public function update(UpdateAccountRequest $request, string $id)
    {
        $account = Account::notDeleted()->findOrFail($id);

        $validated = $request->validated();

        $account->update($validated);

        return $this->successResponse(
            new AccountResource($account),
            'Compte mis à jour avec succès'
        );
    }

    /**
     * Supprimer un compte (Soft delete)
     * DELETE /api/v1/comptes/{id}
     */
    public function destroy(string $id)
    {
        $account = Account::notDeleted()->findOrFail($id);
        $account->delete();

        return $this->successResponse(
            null,
            'Compte supprimé avec succès'
        );
    }
}
