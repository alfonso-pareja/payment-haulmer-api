<?php

namespace App\DTOs;

readonly class TransactionDTO
{
    public function __construct(
        public float  $amount,
        public string $currency,
        public string $cardNumber,
        public string $cardHolder,
    ) {
    }

    /**
     * Get last 4 digits
     */
    public function getMaskedCardNumber(): string
    {
        $lastFour = substr($this->cardNumber, -4);
        return '****-****-****-' . $lastFour;
    }

    /**
     * Get last digit of card number
     */
    public function getLastDigit(): int
    {
        return (int) substr($this->cardNumber, -1);
    }

}
