<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
   public function  createTransaction(array $data): Transaction;

   public function getTransactions(int $perPage): LengthAwarePaginator;
   public function getAll(): Collection;
}
