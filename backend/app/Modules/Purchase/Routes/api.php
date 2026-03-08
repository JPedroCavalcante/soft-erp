<?php

use App\Modules\Purchase\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('purchases')->group(function () {
    Route::get('/', [PurchaseController::class, 'index']);
    Route::post('/', [PurchaseController::class, 'store']);
    Route::get('/{id}', [PurchaseController::class, 'show']);
});
