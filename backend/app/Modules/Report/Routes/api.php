<?php

use App\Modules\Report\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::get('/sales', [ReportController::class, 'sales']);
    Route::get('/purchases', [ReportController::class, 'purchases']);
    Route::get('/profit', [ReportController::class, 'profit']);
    Route::get('/stock', [ReportController::class, 'stock']);
    Route::post('/export', [ReportController::class, 'export']);
});
