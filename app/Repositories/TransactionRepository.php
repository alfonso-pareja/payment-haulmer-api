<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(
        private Transaction $model
    ) {}

    public function createTransaction(array $data): Transaction
    {
        return $this->model->create([
            ...$data,
            'processed_at' => now(),
        ]);
    }

    public function getTransactions(int $perPage): LengthAwarePaginator
    {
        return $this->model
            ->query()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return $this->model
            ->query()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
