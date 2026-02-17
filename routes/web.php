<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Payment Dashboard API',
        'version' => '1.0.0',
        'documentation' => url('/api/documentation'),
    ]);
});