<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des clients à partir des utilisateurs ayant le rôle 'client'
        $clientUsers = \App\Models\User::where('role', 'client')->get();

        foreach ($clientUsers as $user) {
            \App\Models\Client::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
