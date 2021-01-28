<?php

namespace Jurager\Tracker;

use Jurager\Tracker\Factories\IpProviderFactory;
use Jurager\Tracker\Macros\RouteMacros;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
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
        $this->mergeConfigFrom(
            __DIR__ . '/../config/tracker.php', 'tracker'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/tracker.php' => config_path('tracker.php'),
        ], 'config');

        // Publish controllers
        $this->publishes([
            __DIR__.'/Controllers/TrackingController.stub' => app_path('Http/Controllers/Auth/TrackingController.php'),
        ], 'controllers');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views/auth/list.blade.php' => base_path('resources/views/auth/list.blade.php'),
        ], 'views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register extended Eloquent user provider
        Auth::provider('tracked', function ($app, array $config) {
            return new EloquentUserProviderExtended($app['hash'], $config['model']);
        });

        // Register event subscribers
        Event::subscribe('Jurager\Tracker\Listeners\AuthEventSubscriber');
        Event::subscribe('Jurager\Tracker\Listeners\PassportEventSubscriber');
        Event::subscribe('Jurager\Tracker\Listeners\SanctumEventSubscriber');

        // Register route macros
        Route::mixin(new RouteMacros);

        // Register Blade directives
        Blade::if('tracked', function () {
            return method_exists(request()->user(), 'logins');
        });

        Blade::if('ipLookup', function () {
            return IpProviderFactory::ipLookupEnabled();
        });
    }
}
