<?php

namespace Tests\Unit;

use App\DTOs\TransactionDTO;
use App\Models\Transaction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FakeAcquirerLogicTest extends TestCase
{
    #[Test]
    #[DataProvider('cardNumberProvider')]
    public function it_determines_status_based_on_last_digit(string $cardNumber, string $expectedStatus): void
    {
        $dto = new TransactionDTO(
            amount: 100.00,
            currency: 'USD',
            cardNumber: $cardNumber,
            cardHolder: 'Test User',
        );

        $lastDigit = $dto->getLastDigit();

        $actualStatus = ($lastDigit % 2 === 0)
            ? Transaction::STATUS_APPROVED
            : Transaction::STATUS_REJECTED;

        $this->assertEquals($expectedStatus, $actualStatus);
    }

    public static function cardNumberProvider(){
        return [
            'termina en 0 (par)'  => ['4111111111111110', Transaction::STATUS_APPROVED],
            'termina en 2 (par)'  => ['4111111111111112', Transaction::STATUS_APPROVED],
            'termina en 4 (par)'  => ['4111111111111114', Transaction::STATUS_APPROVED],
            'termina en 6 (par)'  => ['4111111111111116', Transaction::STATUS_APPROVED],
            'termina en 8 (par)'  => ['4111111111111118', Transaction::STATUS_APPROVED],
            'termina en 1 (impar)'=> ['4111111111111111', Transaction::STATUS_REJECTED],
            'termina en 3 (impar)'=> ['4111111111111113', Transaction::STATUS_REJECTED],
            'termina en 5 (impar)'=> ['4111111111111115', Transaction::STATUS_REJECTED],
            'termina en 7 (impar)'=> ['4111111111111117', Transaction::STATUS_REJECTED],
            'termina en 9 (impar)'=> ['4111111111111119', Transaction::STATUS_REJECTED],
        ];
    }

    #[Test]
    public function dto_masks_card_number_correctly(): void
    {
        $testCases = [
            '4111111111111112' => '****-****-****-1112',
            '4532015112830366' => '****-****-****-0366',
            '5425233430109903' => '****-****-****-9903',
            '378282246310005'  => '****-****-****-0005',
        ];

        foreach ($testCases as $cardNumber => $expectedMasked) {
            $dto = new TransactionDTO(
                amount: 100,
                currency: 'USD',
                cardNumber: $cardNumber,
                cardHolder: 'Test User',
            );

            $this->assertEquals(
                $expectedMasked,
                $dto->getMaskedCardNumber(),
                "Failed masking for card: {$cardNumber}"
            );
        }
    }

    #[Test]
    public function dto_extracts_last_digit_correctly(): void
    {
        $testCases = [
            '4111111111111110' => 0,
            '4111111111111111' => 1,
            '4111111111111112' => 2,
            '4111111111111119' => 9,
        ];

        foreach ($testCases as $cardNumber => $expectedDigit) {
            $dto = new TransactionDTO(
                amount: 100,
                currency: 'USD',
                cardNumber: $cardNumber,
                cardHolder: 'Test User',
            );

            $this->assertEquals(
                $expectedDigit,
                $dto->getLastDigit(),
                "Failed extracting last digit from: {$cardNumber}"
            );
        }
    }

    #[Test]
    public function dto_is_readonly(): void
    {
        $dto = new TransactionDTO(
            amount: 100,
            currency: 'USD',
            cardNumber: '4111111111111112',
            cardHolder: 'Test User',
        );

        $this->assertEquals(100, $dto->amount);
        $this->assertEquals('USD', $dto->currency);
        $this->assertEquals('4111111111111112', $dto->cardNumber);
        $this->assertEquals('Test User', $dto->cardHolder);
    }

    #[Test]
    public function transaction_model_constants_are_defined(): void
    {
        $this->assertEquals('approved', Transaction::STATUS_APPROVED);
        $this->assertEquals('rejected', Transaction::STATUS_REJECTED);
    }

}
