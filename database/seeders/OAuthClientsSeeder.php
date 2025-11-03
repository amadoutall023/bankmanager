<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OAuthClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un client first-party pour l'API BankManager
        DB::table('oauth_clients')->insert([
            'id' => Str::uuid(),
            'name' => 'BankManager API Client',
            'secret' => Str::random(40),
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["personal_access"]',
            'revoked' => "false",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un client password grant pour l'authentification
        DB::table('oauth_clients')->insert([
            'id' => Str::uuid(),
            'name' => 'BankManager Password Grant Client',
            'secret' => Str::random(40),
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["password","refresh_token"]',
            'revoked' => "false",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}