<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'account_id' => \App\Models\Account::inRandomOrder()->first()->id,
            'client_id' => \App\Models\Client::inRandomOrder()->first()->id,
            'type' => fake()->randomElement(['credit', 'debit']),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
            'transaction_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
