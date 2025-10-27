<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un admin par défaut
        $adminUser = \App\Models\User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@banque.example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Admin123!@#'),
            'phone' => '+221771234567',
            'address' => 'Dakar, Sénégal',
            'role' => 'admin',
            'is_verified' => true,
        ]);

        \App\Models\Admin::create([
            'user_id' => $adminUser->id,
        ]);

        // Créer des admins à partir des utilisateurs ayant le rôle 'admin' (si existants)
        $adminUsers = \App\Models\User::where('role', 'admin')->where('id', '!=', $adminUser->id)->get();

        foreach ($adminUsers as $user) {
            \App\Models\Admin::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
