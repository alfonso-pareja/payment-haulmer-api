<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $cardNumber = $this->faker->creditCardNumber();
        $lastFour = substr($cardNumber, -4);

        // Determine status based on last digit (fake acquirer logic)
        $lastDigit = (int) substr($cardNumber, -1);
        $status = ($lastDigit % 2 === 0) ? Transaction::STATUS_APPROVED : Transaction::STATUS_REJECTED;

        return [
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'CLP']),
            'card_number_masked' => '****-****-****-' . $lastFour,
            'card_holder' => $this->faker->name(),
            'status' => $status,
            'processed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the transaction is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_APPROVED,
            'card_number_masked' => '****-****-****-' . $this->faker->randomElement(['1234', '5678', '9012', '4568']),
        ]);
    }

    /**
     * Indicate that the transaction is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_REJECTED,
            'card_number_masked' => '****-****-****-' . $this->faker->randomElement(['1235', '5679', '9013', '4567']),
        ]);
    }
}
