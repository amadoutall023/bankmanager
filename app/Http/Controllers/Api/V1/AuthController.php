<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponseTrait;

/**
 * @OA\Tag(
 *     name="Authentification",
 *     description="Gestion de l'authentification"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token JWT obtenu via /auth/login"
 * )
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Authentification"},
     *     summary="Connexion utilisateur",
     *     description="Authentifie un utilisateur et retourne les tokens",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", description="Email de l'utilisateur", example="admin@banque.example.com"),
     *             @OA\Property(property="password", type="string", description="Mot de passe", example="Admin123!@#")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="role", type="string")
     *                 ),
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="refresh_token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

        // Créer un token Passport
        $token = $user->createToken('API Token')->accessToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 'Connexion réussie');
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Authentification"},
     *     summary="Rafraîchir le token d'accès",
     *     description="Génère un nouveau token d'accès à partir du refresh token",
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Refresh token invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token') ?? $request->input('refresh_token');

        if (!$refreshToken) {
            return $this->errorResponse('Refresh token manquant', 401);
        }

        // Vérifier et trouver le token
        $token = \Laravel\Passport\Token::findToken($refreshToken);

        if (!$token || $token->revoked || $token->expires_at < now()) {
            return $this->errorResponse('Refresh token invalide', 401);
        }

        $user = $token->user;

        // Créer un nouveau token d'accès
        $accessToken = $user->createToken('Access Token', [], now()->addHour())->accessToken;

        return $this->successResponse([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 'Token rafraîchi avec succès');
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Authentification"},
     *     summary="Déconnexion utilisateur",
     *     description="Invalide tous les tokens de l'utilisateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Révoquer tous les tokens de l'utilisateur
            $user->tokens->each(function ($token) {
                $token->revoke();
            });

            // Supprimer le cookie refresh_token
            Cookie::queue(Cookie::forget('refresh_token'));
        }

        return $this->successResponse(null, 'Déconnexion réussie');
    }
}
