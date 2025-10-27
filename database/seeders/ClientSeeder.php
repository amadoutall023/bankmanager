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
        // Créer quelques clients de test
        $clients = [
            [
                'name' => 'Amadou Diallo',
                'email' => 'amadou.diallo@example.com',
                'phone' => '+221771234568',
                'address' => 'Dakar, Sénégal',
            ],
            [
                'name' => 'Fatou Sow',
                'email' => 'fatou.sow@example.com',
                'phone' => '+221781234569',
                'address' => 'Thiès, Sénégal',
            ],
            [
                'name' => 'Mamadou Ba',
                'email' => 'mamadou.ba@example.com',
                'phone' => '+221701234570',
                'address' => 'Saint-Louis, Sénégal',
            ],
        ];

        foreach ($clients as $clientData) {
            $user = \App\Models\User::create([
                'name' => $clientData['name'],
                'email' => $clientData['email'],
                'password' => \Illuminate\Support\Facades\Hash::make('Client123!@#'),
                'phone' => $clientData['phone'],
                'address' => $clientData['address'],
                'role' => 'client',
                'is_verified' => true,
            ]);

            \App\Models\Client::create([
                'user_id' => $user->id,
            ]);
        }

        // Créer des clients à partir des utilisateurs ayant le rôle 'client' (si existants)
        $clientUsers = \App\Models\User::where('role', 'client')
            ->whereNotIn('email', array_column($clients, 'email'))
            ->get();

        foreach ($clientUsers as $user) {
            \App\Models\Client::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
