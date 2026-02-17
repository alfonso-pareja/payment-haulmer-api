<?php

namespace App\Providers;

use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TransactionRepositoryInterface::class,
            TransactionRepository::class
        );
    }
}
