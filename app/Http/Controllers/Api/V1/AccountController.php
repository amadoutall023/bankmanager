<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountCollection;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\BlockAccountRequest;
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
     * @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             required={"type", "solde", "soldeInitial", "devise", "client"},
      *             @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, description="Type de compte", example="epargne"),
      *             @OA\Property(property="solde", type="number", format="float", description="Solde initial", example=100000),
      *             @OA\Property(property="soldeInitial", type="number", format="float", description="Solde initial", example=100000),
      *             @OA\Property(property="devise", type="string", description="Devise", example="FCFA"),
      *             @OA\Property(
      *                 property="client",
      *                 type="object",
      *                 description="Informations du client",
      *                 @OA\Property(property="id", type="integer", description="ID du client existant (0 ou null pour nouveau client)", example=0),
      *                 @OA\Property(property="titulaire", type="string", description="Nom du titulaire", example="Cheikh Sy"),
      *                 @OA\Property(property="email", type="string", format="email", description="Email du client", example="cheikh.sy@example.com"),
      *                 @OA\Property(property="telephone", type="string", description="Numéro de téléphone", example="+221771114567"),
      *                 @OA\Property(property="adresse", type="string", description="Adresse du client", example="Dakar, Sénégal")
      *             )
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

        // Vérifier si le client existe ou doit être créé
        $clientId = $validated['client']['id'] ?? null;
        $client = null;

        if ($clientId) {
            // Utiliser le client existant
            $client = Client::findOrFail($clientId);
        } else {
            // Créer un nouveau client et utilisateur
            $generatedPassword = $this->generatePassword();
            $verificationCode = $this->generateVerificationCode();

            $user = User::create([
                'name' => $validated['client']['titulaire'],
                'email' => $validated['client']['email'],
                'password' => Hash::make($generatedPassword),
                'phone' => $validated['client']['telephone'],
                'address' => $validated['client']['adresse'],
                'role' => 'client',
                'verification_code' => $verificationCode,
                'verification_code_expires_at' => now()->addMinutes(15),
                'is_verified' => "false",
            ]);

            $client = Client::create([
                'user_id' => $user->id,
            ]);

            // Envoyer email d'authentification
            $this->sendAuthenticationEmail($user, $generatedPassword);

            // Envoyer SMS avec le code
            $this->sendVerificationSMS($user, $verificationCode);
        }

        // Générer numéro de compte unique
        $accountNumber = $this->generateAccountNumber();

        // Créer le compte
        $account = Account::create([
            'client_id' => $client->id,
            'account_number' => $accountNumber,
            'type' => $validated['type'],
            'balance' => $validated['solde'],
            'status' => 'active',
        ]);

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
     * @OA\Patch(
     *     path="/comptes/{id}",
     *     tags={"Comptes"},
     *     summary="Modifier les informations d'un compte bancaire",
     *     description="Met à jour les informations du titulaire et/ou les informations client d'un compte bancaire existant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             @OA\Property(property="titulaire", type="string", description="Nouveau nom du titulaire", example="Cheikh Sy"),
      *             @OA\Property(
      *                 property="informationsClient",
      *                 type="object",
      *                 description="Informations client à mettre à jour",
      *                 @OA\Property(property="telephone", type="string", description="Nouveau numéro de téléphone", example="+221775168040"),
      *                 @OA\Property(property="email", type="string", format="email", description="Nouvelle adresse email", example="nouveau.email@example.com"),
      *                 @OA\Property(property="password", type="string", description="Nouveau mot de passe", example="MotDePasse@123!")
      *             )
      *         )
      *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations du compte mises à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Informations du compte mises à jour avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Cheikh Sy"),
     *                 @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, example="cheque"),
     *                 @OA\Property(property="solde", type="number", format="float", example=500000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, example="actif"),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-10-19T11:15:00Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="titulaire", type="array", @OA\Items(type="string"), example={"Le nom du titulaire est requis"}),
     *                 @OA\Property(property="informationsClient.telephone", type="array", @OA\Items(type="string"), example={"Le téléphone doit être un numéro sénégalais valide"}),
     *                 @OA\Property(property="informationsClient.email", type="array", @OA\Items(type="string"), example={"L'email doit être valide"})
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateAccountRequest $request, string $id)
    {
        Log::info('AccountController update called', ['id' => $id, 'data' => $request->all()]);
        $account = Account::with('client.user')->notDeleted()->findOrFail($id);
        $validated = $request->validated();

        // Mise à jour du titulaire si fourni
        if (isset($validated['titulaire'])) {
            $account->client->user->update([
                'name' => $validated['titulaire']
            ]);
        }

        // Mise à jour des informations client si fournies
        if (isset($validated['informationsClient'])) {
            $clientData = $validated['informationsClient'];

            $userUpdateData = [];

            if (isset($clientData['telephone'])) {
                $userUpdateData['phone'] = $clientData['telephone'];
            }

            if (isset($clientData['email'])) {
                $userUpdateData['email'] = $clientData['email'];
            }

            if (isset($clientData['password'])) {
                $userUpdateData['password'] = Hash::make($clientData['password']);
            }

            if (!empty($userUpdateData)) {
                $account->client->user->update($userUpdateData);
            }
        }

        // Recharger l'account avec les relations mises à jour
        $account->refresh();

        return $this->successResponse(
            new AccountResource($account),
            'Compte mis à jour avec succès'
        );
    }

    /**
     * @OA\Post(
     *     path="/comptes/{compteId}/bloquer",
     *     tags={"Comptes"},
     *     summary="Bloquer un compte bancaire",
     *     description="Bloque un compte bancaire pour une durée déterminée avec un motif spécifique",
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"dureeBlocage", "motifBlocage"},
     *             @OA\Property(property="dureeBlocage", type="integer", description="Durée de blocage en jours", example=30, minimum=1, maximum=365),
     *             @OA\Property(property="motifBlocage", type="string", description="Motif du blocage", example="Suspicion de fraude", maxLength=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte bloqué avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="statut", type="string", example="bloque"),
     *                 @OA\Property(property="dateBlocage", type="string", format="date-time", example="2025-10-27T12:00:00Z"),
     *                 @OA\Property(property="dateFinBlocage", type="string", format="date-time", example="2025-11-26T12:00:00Z"),
     *                 @OA\Property(property="motifBlocage", type="string", example="Suspicion de fraude")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="dureeBlocage", type="array", @OA\Items(type="string"), example={"La durée de blocage est obligatoire"}),
     *                 @OA\Property(property="motifBlocage", type="array", @OA\Items(type="string"), example={"Le motif de blocage est obligatoire"})
     *             )
     *         )
     *     )
     * )
     */
    public function block(BlockAccountRequest $request, string $compteId)
    {
        $account = Account::notDeleted()->findOrFail($compteId);
        $validated = $request->validated();

        // Calculer la date d'expiration du blocage
        $blockingExpiresAt = now()->addDays($validated['dureeBlocage']);

        // Bloquer le compte
        $account->update([
            'status' => 'inactive',
            'blocked_at' => now(),
            'blocking_expires_at' => $blockingExpiresAt,
            'blocking_reason' => $validated['motifBlocage'],
        ]);

        return $this->successResponse([
            'id' => $account->id,
            'numeroCompte' => $account->account_number,
            'statut' => 'bloque',
            'dateBlocage' => $account->blocked_at->format('Y-m-d\TH:i:s\Z'),
            'dateFinBlocage' => $account->blocking_expires_at->format('Y-m-d\TH:i:s\Z'),
            'motifBlocage' => $account->blocking_reason,
        ], 'Compte bloqué avec succès');
    }

    /**
     * @OA\Delete(
     *     path="/comptes/{id}",
     *     tags={"Comptes"},
     *     summary="Fermer un compte bancaire",
     *     description="Effectue une suppression logique (soft delete) du compte bancaire en le marquant comme fermé",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte (UUID)",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte fermé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="statut", type="string", example="ferme"),
     *                 @OA\Property(property="dateFermeture", type="string", format="date-time", example="2025-10-19T11:15:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $account = Account::notDeleted()->findOrFail($id);

        // Effectuer le soft delete
        $account->delete();

        // Retourner la réponse avec les informations demandées
        return $this->successResponse([
            'id' => $account->id,
            'numeroCompte' => $account->account_number,
            'statut' => 'ferme',
            'dateFermeture' => now()->format('Y-m-d\TH:i:s\Z')
        ], 'Compte supprimé avec succès');
    }

    /**
     * Génère un mot de passe aléatoire sécurisé
     */
    private function generatePassword(): string
    {
        return Str::random(12) . rand(100, 999);
    }

    /**
     * Génère un code de vérification à 6 chiffres
     */
    private function generateVerificationCode(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Génère un numéro de compte unique
     */
    private function generateAccountNumber(): string
    {
        do {
            $number = 'C' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $number)->exists());

        return $number;
    }

    /**
     * Envoie un email d'authentification avec le mot de passe
     */
    private function sendAuthenticationEmail(User $user, string $password): void
    {
        // Pour cette implémentation simplifiée, on simule l'envoi
        // En production, utiliser un service d'email comme Mailgun, SendGrid, etc.
        Log::info("Email d'authentification envoyé à {$user->email} avec mot de passe: {$password}");

        // Exemple avec Laravel Mail (à implémenter):
        // Mail::to($user->email)->send(new AuthenticationEmail($user, $password));
    }

    /**
     * Envoie un SMS avec le code de vérification
     */
    private function sendVerificationSMS(User $user, string $code): void
    {
        // Pour cette implémentation simplifiée, on simule l'envoi
        // En production, utiliser un service SMS comme Twilio, Africa's Talking, etc.
        Log::info("SMS envoyé au {$user->phone} avec code: {$code}");

        // Exemple avec un service SMS (à implémenter):
        // $smsService->send($user->phone, "Votre code de vérification: {$code}");
    }
}
