<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Auto-create SQLite database file if connection is sqlite and file doesn't exist
        $defaultDb = config('database.default');
        if ($defaultDb === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if ($dbPath && !file_exists($dbPath)) {
                $dir = dirname($dbPath);
                if (!file_exists($dir)) {
                    @mkdir($dir, 0755, true);
                }
                @touch($dbPath);
            }
        }
    }
}
