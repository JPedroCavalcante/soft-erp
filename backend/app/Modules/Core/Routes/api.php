<?php

use App\Modules\Core\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core Module API Routes
|--------------------------------------------------------------------------
|
| This file contains all routes for the Core module.
| Routes are automatically prefixed with '/api/core'
|
*/

/**
 * Settings endpoints
 *
 * @group Core Module
 * @subgroup Settings
 */
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])
        ->name('index');

    Route::get('/{key}', [SettingController::class, 'show'])
        ->name('show');

    Route::post('/', [SettingController::class, 'store'])
        ->name('store');

    Route::put('/{key}', [SettingController::class, 'update'])
        ->name('update');

    Route::delete('/{key}', [SettingController::class, 'destroy'])
        ->name('destroy');
});
