<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TransactionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TransactionRepository::class);
    }

   #[Test]
    public function it_creates_transaction_with_processed_at_timestamp(): void
    {
        $data = [
            'amount'             => 100.50,
            'currency'           => 'USD',
            'card_number_masked' => '****-****-****-1112',
            'card_holder'        => 'John Doe',
            'status'             => Transaction::STATUS_APPROVED,
        ];

        $transaction = $this->repository->createTransaction($data);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertNotNull($transaction->processed_at);
        $this->assertDatabaseHas('transactions', [
            'id'     => $transaction->id,
            'amount' => 100.50,
        ]);
    }

   #[Test]
    public function it_retrieves_all_transactions_ordered_by_created_at_desc(): void
    {
        $first = Transaction::factory()->create(['created_at' => now()->subDays(3)]);
        $second = Transaction::factory()->create(['created_at' => now()->subDay()]);
        $third = Transaction::factory()->create(['created_at' => now()]);

        $result = $this->repository->getAll();

        $this->assertCount(3, $result);
        $this->assertEquals($third->id, $result[0]->id);
        $this->assertEquals($second->id, $result[1]->id);
        $this->assertEquals($first->id, $result[2]->id);
    }

   #[Test]
    public function it_returns_empty_collection_when_no_transactions_exist(): void
    {
        $result = $this->repository->getAll();

        $this->assertCount(0, $result);
    }

   #[Test]
    public function it_paginates_transactions(): void
    {
        Transaction::factory()->count(25)->create();

        $paginated = $this->repository->getTransactions(10);

        $this->assertEquals(25, $paginated->total());
        $this->assertEquals(10, $paginated->perPage());
        $this->assertCount(10, $paginated->items());
    }

   #[Test]
    public function it_respects_per_page_parameter(): void
    {
        Transaction::factory()->count(20)->create();

        $paginated = $this->repository->getTransactions(5);

        $this->assertEquals(5, $paginated->perPage());
        $this->assertEquals(4, $paginated->lastPage());
        $this->assertCount(5, $paginated->items());
    }

   #[Test]
    public function paginated_transactions_are_ordered_by_most_recent(): void
    {
        $old = Transaction::factory()->create(['created_at' => now()->subDays(10)]);
        $recent = Transaction::factory()->create(['created_at' => now()]);

        $paginated = $this->repository->getTransactions(10);

        $items = $paginated->items();
        $this->assertEquals($recent->id, $items[0]->id);
        $this->assertEquals($old->id, $items[1]->id);
    }
}
