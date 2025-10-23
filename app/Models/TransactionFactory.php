<?php
namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'account_id' => Account::factory(),
            'client_id' => Client::factory(),
            'type' => $this->faker->randomElement(['credit','debit']),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'status' => 'completed',
            'transaction_date' => $this->faker->dateTimeThisYear,
        ];
    }
}
