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
        // Créer un utilisateur de test
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => ['id', 'name', 'email', 'role'],
                        'access_token',
                        'refresh_token',
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

        // Simuler l'authentification
        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/v1/auth/logout');

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

        // Créer un token de refresh
        $refreshToken = $user->createToken('Refresh Token', [], now()->addDays(30))->accessToken;

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in',
                    ],
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'token_type' => 'Bearer',
                        'expires_in' => 3600,
                    ],
                ]);
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
                    'message' => 'Non authentifié. Token d\'accès requis.',
                ]);
    }

    /**
     * Test d'accès à une route protégée avec authentification
     */
    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();

        // Simuler l'authentification
        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/v1/comptes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data',
                ]);
    }
}
