<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations from all modules
        $this->loadModuleMigrations();
    }

    /**
     * Load migrations from all modules dynamically.
     */
    protected function loadModuleMigrations(): void
    {
        $modulesPath = app_path('Modules');

        if (!is_dir($modulesPath)) {
            return;
        }

        $modules = glob($modulesPath . '/*', GLOB_ONLYDIR);

        foreach ($modules as $modulePath) {
            $migrationPath = $modulePath . '/Database/Migrations';

            if (is_dir($migrationPath)) {
                $this->loadMigrationsFrom($migrationPath);
            }
        }
    }
}
