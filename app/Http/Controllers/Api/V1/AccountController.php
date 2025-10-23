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

/**
 * @OA\Info(
 *     title="BankManager API",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement"
 * )
 *
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires"
 * )
 */

class AccountController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Schema(
     *     schema="Account",
     *     type="object",
     *     @OA\Property(property="id", type="string", format="uuid", description="ID unique du compte"),
     *     @OA\Property(property="account_number", type="string", description="Numéro de compte unique"),
     *     @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, description="Type de compte"),
     *     @OA\Property(property="balance", type="number", format="float", description="Solde du compte"),
     *     @OA\Property(property="status", type="string", enum={"active", "inactive", "closed"}, description="Statut du compte"),
     *     @OA\Property(
     *         property="client",
     *         type="object",
     *         @OA\Property(property="id", type="integer", description="ID du client"),
     *         @OA\Property(
     *             property="user",
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="ID de l'utilisateur"),
     *             @OA\Property(property="name", type="string", description="Nom du titulaire"),
     *             @OA\Property(property="email", type="string", format="email", description="Email du titulaire")
     *         )
     *     ),
     *     @OA\Property(property="created_at", type="string", format="date-time"),
     *     @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     *
     * @OA\Schema(
     *     schema="AccountCollection",
     *     type="object",
     *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Account")),
     *     @OA\Property(
     *         property="pagination",
     *         type="object",
     *         @OA\Property(property="currentPage", type="integer"),
     *         @OA\Property(property="totalPages", type="integer"),
     *         @OA\Property(property="totalItems", type="integer"),
     *         @OA\Property(property="itemsPerPage", type="integer"),
     *         @OA\Property(property="hasNext", type="boolean"),
     *         @OA\Property(property="hasPrevious", type="boolean")
     *     ),
     *     @OA\Property(
     *         property="links",
     *         type="object",
     *         @OA\Property(property="self", type="string"),
     *         @OA\Property(property="next", type="string", nullable=true),
     *         @OA\Property(property="first", type="string"),
     *         @OA\Property(property="last", type="string")
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/comptes",
     *     tags={"Comptes"},
     *     summary="Lister tous les comptes bancaires",
     *     description="Récupère la liste de tous les comptes avec possibilité de filtrage et pagination",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Statut du compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de compte ou nom du titulaire",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", description="Collection des comptes avec pagination")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/comptes",
     *     tags={"Comptes"},
     *     summary="Créer un nouveau compte bancaire",
     *     description="Crée un nouveau compte bancaire pour un client existant",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id", "type"},
     *             @OA\Property(property="client_id", type="integer", description="ID du client"),
     *             @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, description="Type de compte"),
     *             @OA\Property(property="balance", type="number", format="float", description="Solde initial", default=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", description="Détails du compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/comptes/{id}",
     *     tags={"Comptes"},
     *     summary="Afficher un compte spécifique",
     *     description="Récupère les détails d'un compte bancaire par son ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", description="Détails du compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/comptes/{id}",
     *     tags={"Comptes"},
     *     summary="Modifier un compte bancaire",
     *     description="Met à jour les informations d'un compte bancaire existant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"epargne", "cheque"}),
     *             @OA\Property(property="balance", type="number", format="float", minimum=0),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive", "closed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object", description="Détails du compte mis à jour")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/comptes/{id}",
     *     tags={"Comptes"},
     *     summary="Supprimer un compte bancaire",
     *     description="Supprime un compte bancaire (soft delete)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
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
