<?php

namespace Jurager\Tracker;

use Illuminate\Support\ServiceProvider;

class TrackerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(__DIR__.'/../config/tracker.php', 'tracker');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/tracker.php' => config_path('tracker.php')
        ], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
