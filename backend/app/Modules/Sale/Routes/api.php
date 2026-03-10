<?php

use App\Modules\Sale\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('sales')->group(function () {
    Route::get('/', [SaleController::class, 'index']);
    Route::post('/', [SaleController::class, 'store']);
    Route::get('/{id}', [SaleController::class, 'show']);
    Route::post('/{id}/cancel', [SaleController::class, 'cancel']);
    Route::delete('/{id}', [SaleController::class, 'destroy']);
});
