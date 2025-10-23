<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'client_id' => \App\Models\Client::inRandomOrder()->first()->id,
            'account_number' => 'C' . fake()->unique()->numberBetween(10000000, 99999999),
            'type' => fake()->randomElement(['epargne', 'cheque']),
            'balance' => fake()->randomFloat(2, 0, 1000000),
            'status' => fake()->randomElement(['active', 'inactive', 'closed']),
        ];
    }
}
