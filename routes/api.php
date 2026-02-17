<?php

use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/all', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions', [TransactionController::class, 'getTransactions'])->name('transactions.getTransactions');
});

Route::get('/health', function () {
    return response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service'   => 'Payment Dashboard API',
    ]);
})->name('health');
