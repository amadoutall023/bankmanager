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
        // Créer des admins à partir des utilisateurs ayant le rôle 'admin'
        $adminUsers = \App\Models\User::where('role', 'admin')->get();

        foreach ($adminUsers as $user) {
            \App\Models\Admin::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
