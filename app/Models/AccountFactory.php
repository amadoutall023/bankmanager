<?php
namespace Database\Factories;

use App\Models\Account;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition()
    {
        return [
            'client_id' => Client::factory(),
            'account_number' => 'ACC'.$this->faker->unique()->numberBetween(10000000, 99999999),
            'type' => $this->faker->randomElement(['epargne','cheque']),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'status' => 'active',
        ];
    }
}
