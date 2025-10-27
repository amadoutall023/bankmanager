<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de création d'un compte avec un nouveau client
     */
    public function test_admin_can_create_account_with_new_client(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'api');

        $accountData = [
            'type' => 'cheque',
            'soldeInitial' => 500000,
            'devise' => 'FCFA',
            'solde' => 500000,
            'client' => [
                'titulaire' => 'Cheikh Sy',
                'email' => 'cheikh.sy@example.com',
                'telephone' => '+221771234567',
                'adresse' => 'Dakar, Sénégal',
            ],
        ];

        $response = $this->postJson('/api/v1/comptes', $accountData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'numeroCompte',
                        'titulaire',
                        'type',
                        'solde',
                        'devise',
                        'dateCreation',
                        'statut',
                        'metadata',
                    ],
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Compte créé avec succès',
                    'data' => [
                        'titulaire' => 'Cheikh Sy',
                        'type' => 'cheque',
                        'solde' => 500000,
                        'devise' => 'FCFA',
                        'statut' => 'actif',
                    ],
                ]);

        // Vérifier que l'utilisateur et le client ont été créés
        $this->assertDatabaseHas('users', [
            'name' => 'Cheikh Sy',
            'email' => 'cheikh.sy@example.com',
            'phone' => '+221771234567',
            'role' => 'client',
        ]);

        $this->assertDatabaseHas('clients', [
            'user_id' => User::where('email', 'cheikh.sy@example.com')->first()->id,
        ]);

        // Vérifier que le compte a été créé
        $this->assertDatabaseHas('accounts', [
            'type' => 'cheque',
            'balance' => 500000,
            'status' => 'active',
        ]);
    }

    /**
     * Test de création d'un compte avec un client existant
     */
    public function test_admin_can_create_account_with_existing_client(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $clientUser = User::factory()->create(['role' => 'client']);
        $client = Client::factory()->create(['user_id' => $clientUser->id]);

        $this->actingAs($admin, 'api');

        $accountData = [
            'type' => 'epargne',
            'soldeInitial' => 100000,
            'devise' => 'FCFA',
            'solde' => 100000,
            'client' => [
                'id' => $client->id,
            ],
        ];

        $response = $this->postJson('/api/v1/comptes', $accountData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Compte créé avec succès',
                    'data' => [
                        'titulaire' => $clientUser->name,
                        'type' => 'epargne',
                        'solde' => 100000,
                        'devise' => 'FCFA',
                        'statut' => 'actif',
                    ],
                ]);

        // Vérifier que le compte a été créé pour le client existant
        $this->assertDatabaseHas('accounts', [
            'client_id' => $client->id,
            'type' => 'epargne',
            'balance' => 100000,
            'status' => 'active',
        ]);
    }

    /**
     * Test de validation des données d'entrée
     */
    public function test_account_creation_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'api');

        // Test avec solde insuffisant
        $response = $this->postJson('/api/v1/comptes', [
            'type' => 'cheque',
            'soldeInitial' => 5000, // Moins de 10000
            'devise' => 'FCFA',
            'solde' => 5000,
            'client' => [
                'titulaire' => 'Test User',
                'email' => 'test@example.com',
                'telephone' => '+221771234567',
                'adresse' => 'Dakar, Sénégal',
            ],
        ]);

        $response->assertStatus(422);

        // Test avec téléphone invalide
        $response = $this->postJson('/api/v1/comptes', [
            'type' => 'cheque',
            'soldeInitial' => 50000,
            'devise' => 'FCFA',
            'solde' => 50000,
            'client' => [
                'titulaire' => 'Test User',
                'email' => 'test@example.com',
                'telephone' => '123456789', // Téléphone invalide
                'adresse' => 'Dakar, Sénégal',
            ],
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test de récupération des comptes
     */
    public function test_user_can_list_accounts(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/v1/comptes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data',
                ]);
    }

    /**
     * Test de récupération d'un compte spécifique
     */
    public function test_user_can_show_specific_account(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $account = Account::factory()->create(['client_id' => $client->id]);

        $this->actingAs($user, 'api');

        $response = $this->getJson("/api/v1/comptes/{$account->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Compte récupéré avec succès',
                    'data' => [
                        'id' => $account->id,
                        'numeroCompte' => $account->account_number,
                        'titulaire' => $user->name,
                        'type' => $account->type,
                        'solde' => $account->balance,
                        'devise' => 'FCFA',
                        'statut' => $account->status === 'active' ? 'actif' : 'inactif',
                    ],
                ]);
    }

    /**
     * Test de mise à jour d'un compte
     */
    public function test_admin_can_update_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $account = Account::factory()->create(['type' => 'cheque', 'status' => 'active']);

        $this->actingAs($admin, 'api');

        $updateData = [
            'type' => 'epargne',
            'balance' => 750000,
            'status' => 'inactive',
        ];

        $response = $this->patchJson("/api/v1/comptes/{$account->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Compte mis à jour avec succès',
                ]);

        // Vérifier que le compte a été mis à jour
        $account->refresh();
        $this->assertEquals('epargne', $account->type);
        $this->assertEquals(750000, $account->balance);
        $this->assertEquals('inactive', $account->status);
    }

    /**
     * Test de suppression d'un compte
     */
    public function test_admin_can_delete_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $account = Account::factory()->create();

        $this->actingAs($admin, 'api');

        $response = $this->deleteJson("/api/v1/comptes/{$account->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Compte supprimé avec succès',
                ]);

        // Vérifier que le compte est soft deleted
        $this->assertSoftDeleted($account);
    }
}
