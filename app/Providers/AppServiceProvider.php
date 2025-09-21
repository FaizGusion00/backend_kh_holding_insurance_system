<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        // Load versioned API routes file
        $routesPath = base_path('routes/api_v1.php');
        if (file_exists($routesPath)) {
            require $routesPath;
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\NormalizeAgentCodes::class,
            ]);
        }
    }
}
