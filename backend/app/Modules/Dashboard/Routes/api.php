<?php

use App\Modules\Dashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('metrics')->group(function () {
    Route::get('/', [DashboardController::class, 'metrics']);
});
