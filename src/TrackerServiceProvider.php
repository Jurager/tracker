<?php

namespace Jurager\Tracker;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class TrackerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge default config
        $this->mergeConfigFrom(__DIR__.'/../config/tracker.php', 'tracker');

	    // Register commands
	    $this->commands([
		    Commands\Purge::class,
	    ]);
    }

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 * @throws \ReflectionException
	 */
    public function boot()
    {
        // Publish config
        $this->publishes([__DIR__.'/../config/tracker.php' => config_path('tracker.php')], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
