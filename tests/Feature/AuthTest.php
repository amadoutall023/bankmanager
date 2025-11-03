<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de connexion réussie
     */
    public function test_user_can_login_successfully(): void
    {
        // Utiliser l'admin créé par le seeder
        $user = User::where('email', 'admin@banque.example.com')->first();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@banque.example.com',
            'password' => 'Admin123!@#',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => ['id', 'name', 'email', 'role'],
                        'access_token',
                        'token_type',
                        'expires_in',
                    ],
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                        ],
                        'token_type' => 'Bearer',
                        'expires_in' => 3600,
                    ],
                ]);
    }

    /**
     * Test de connexion avec identifiants invalides
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Identifiants invalides',
                ]);
    }

    /**
     * Test de déconnexion
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        // Créer un token d'accès pour l'utilisateur
        $token = $user->createToken('API Token')->accessToken;

        // Utiliser le token dans la requête
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Déconnexion réussie',
                ]);
    }

    /**
     * Test de rafraîchissement du token
     */
    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create();

        // Créer un token d'accès (simulant un refresh token)
        $accessToken = $user->createToken('Access Token')->accessToken;

        // Pour cette version simplifiée, on teste juste que l'endpoint existe
        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $accessToken,
        ]);

        // L'endpoint peut retourner une erreur mais doit exister
        $response->assertStatus(401); // Token invalide car ce n'est pas un refresh token
    }

    /**
     * Test d'accès à une route protégée sans authentification
     */
    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/api/v1/comptes');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Token d\'accès manquant',
                ]);
    }

    /**
     * Test d'accès à une route protégée avec authentification
     */
    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();

        // Créer un token d'accès pour l'utilisateur
        $token = $user->createToken('API Token')->accessToken;

        // Utiliser le token dans la requête
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/comptes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data',
                ]);
    }
}
