<?php

namespace App\Services;

use App\DTOs\TransactionDTO;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactionRepository
    ) {
    }

    /**
     * Process a payment transaction
     */
    public function processPayment(TransactionDTO $dto): Transaction
    {
        try {
            DB::beginTransaction();

            // Determine transaction status based on fake acquirer logic
            $status = $this->determineFakeAcquirerStatus($dto);

            // Create transaction record
            $transaction = $this->transactionRepository->createTransaction([
                'amount' => $dto->amount,
                'currency' => $dto->currency,
                'card_number_masked' => $dto->getMaskedCardNumber(),
                'card_holder' => $dto->cardHolder,
                'status' => $status,
            ]);

            DB::commit();

            Log::info('Payment processed', [
                'transaction_id' => $transaction->id,
                'status' => $status,
                'amount' => $dto->amount,
            ]);

            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'amount' => $dto->amount,
            ]);

            throw $e;
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->transactionRepository->getAll();
    }

    public function getTransactionHistoryPaginated(int $perPage): LengthAwarePaginator {
        return $this->transactionRepository->getTransactions($perPage);
    }

//
//    /**
//     * Get transaction by ID
//     */
//    public function getTransaction(string $id): ?Transaction
//    {
//        return $this->transactionRepository->find($id);
//    }


    /**
     * Fake Acquirer Logic Implementation
     *
     * @param TransactionDTO $dto
     * @return string approved|rejected
     */
    protected function determineFakeAcquirerStatus(TransactionDTO $dto): string
    {
        $lastDigit = $dto->getLastDigit();

        return ($lastDigit % 2 === 0)
            ? Transaction::STATUS_APPROVED
            : Transaction::STATUS_REJECTED;
    }
}
