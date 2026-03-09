<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * Health check endpoint for monitoring
 */
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'Soft ERP API',
        'version' => config('app.version', '1.0.0'),
        'timestamp' => now()->toISOString(),
    ]);
});

/**
 * Sample authenticated route
 */
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| This is where module-specific routes are automatically loaded.
| Each module should have its own Routes/api.php file that will be
| registered below.
|
| Example:
| - app/Modules/Finance/Routes/api.php
| - app/Modules/Inventory/Routes/api.php
| - app/Modules/Sales/Routes/api.php
|
*/

// Dynamically load all module routes
$modulesPath = app_path('Modules');

if (is_dir($modulesPath)) {
    $modules = glob($modulesPath . '/*', GLOB_ONLYDIR);

    foreach ($modules as $modulePath) {
        $moduleName = basename($modulePath);
        $routeFile = $modulePath . '/Routes/api.php';

        if (file_exists($routeFile)) {
            if ($moduleName === 'Auth') {
                Route::name('auth.')->group($routeFile);
            } else {
                Route::prefix(strtolower($moduleName))
                    ->name(strtolower($moduleName) . '.')
                    ->middleware('auth:sanctum')
                    ->group($routeFile);
            }
        }
    }
}
