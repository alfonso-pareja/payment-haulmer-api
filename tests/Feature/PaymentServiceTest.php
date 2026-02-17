<?php

namespace Tests\Feature;

use App\DTOs\TransactionDTO;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentServiceTest extends TestCase {
    use RefreshDatabase;
    private PaymentService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = app(PaymentService::class);
    }

    #[Test]
    public function it_creates_transaction_with_correct_status_for_even_card(): void
    {
        $dto = new TransactionDTO(
            amount: 100.50,
            currency: 'USD',
            cardNumber: '4111111111111112', // par
            cardHolder: 'John Doe',
        );

        $transaction = $this->service->processPayment($dto);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals(Transaction::STATUS_APPROVED, $transaction->status);
        $this->assertEquals(100.50, $transaction->amount);
        $this->assertEquals('USD', $transaction->currency);
        $this->assertEquals('****-****-****-1112', $transaction->card_number_masked);
        $this->assertEquals('John Doe', $transaction->card_holder);
        $this->assertNotNull($transaction->processed_at);
    }

    #[Test]
    public function it_creates_transaction_with_correct_status_for_odd_card(): void
    {
        $dto = new TransactionDTO(
            amount: 75.25,
            currency: 'EUR',
            cardNumber: '4111111111111111', // impar
            cardHolder: 'Jane Smith',
        );

        $transaction = $this->service->processPayment($dto);

        $this->assertEquals(Transaction::STATUS_REJECTED, $transaction->status);
        $this->assertEquals(75.25, $transaction->amount);
    }

    #[Test]
    public function it_uses_database_transaction(): void
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $dto = new TransactionDTO(
            amount: 100,
            currency: 'USD',
            cardNumber: '4111111111111112',
            cardHolder: 'Test User',
        );

        $this->service->processPayment($dto);
    }

    #[Test]
    public function it_logs_successful_payment(): void
    {
        Log::spy();

        $dto = new TransactionDTO(
            amount: 100,
            currency: 'USD',
            cardNumber: '4111111111111112',
            cardHolder: 'Test User',
        );

        $transaction = $this->service->processPayment($dto);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Payment processed', [
                'transaction_id' => $transaction->id,
                'status'         => 'approved',
                'amount'         => 100.0,
            ]);
    }

    #[Test]
    public function it_retrieves_all_transactions_ordered_by_recent(): void
    {
        $old = Transaction::factory()->create(['created_at' => now()->subDays(5)]);
        $recent = Transaction::factory()->create(['created_at' => now()]);

        $result = $this->service->getTransactionHistory();

        $this->assertCount(2, $result);
        $this->assertEquals($recent->id, $result->first()->id);
        $this->assertEquals($old->id, $result->last()->id);
    }

    #[Test]
    public function it_retrieves_paginated_transactions(): void
    {
        Transaction::factory()->count(25)->create();

        $paginated = $this->service->getTransactionHistoryPaginated(10);

        $this->assertEquals(25, $paginated->total());
        $this->assertEquals(10, $paginated->perPage());
        $this->assertEquals(1, $paginated->currentPage());
        $this->assertEquals(3, $paginated->lastPage());
        $this->assertCount(10, $paginated->items());
    }

    #[Test]
    public function it_persists_transaction_to_database(): void
    {
        $dto = new TransactionDTO(
            amount: 250.75,
            currency: 'CLP',
            cardNumber: '5425233430109903',
            cardHolder: 'Maria Garcia',
        );

        $transaction = $this->service->processPayment($dto);

        $this->assertDatabaseHas('transactions', [
            'id'                 => $transaction->id,
            'amount'             => 250.75,
            'currency'           => 'CLP',
            'card_number_masked' => '****-****-****-9903',
            'card_holder'        => 'Maria Garcia',
        ]);
    }

}
