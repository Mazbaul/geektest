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
    public function definition()
    {
        return [
            'sender_user_id' => $this->faker->randomDigitNot(0),
            'sender_currency' => $this->faker->currencyCode(),
            'sending_amount' => $this->faker->randomFloat(2, 1, 100),
            'receiver_user_id' => $this->faker->randomDigitNot(0),
            'receiver_currency' => $this->faker->currencyCode(),
            'receiving_amount' => $this->faker->randomFloat(2, 1, 100),
            'transaction_at' => now(),
        ];
    }
}
