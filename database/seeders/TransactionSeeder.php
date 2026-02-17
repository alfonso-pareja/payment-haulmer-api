<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 approved transactions
        Transaction::factory()
            ->count(10)
            ->approved()
            ->create();

        // Create 5 rejected transactions
        Transaction::factory()
            ->count(5)
            ->rejected()
            ->create();

        // Create 5 random transactions
        Transaction::factory()
            ->count(5)
            ->create();
    }
}
