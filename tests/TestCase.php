<?php

namespace Jurager\Tracker\Tests;

use ALajusticia\Expirable\ExpirableServiceProvider;
use Jurager\Tracker\TrackerServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        $this->artisan('migrate')->run();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            TrackerServiceProvider::class,
            ExpirableServiceProvider::class,
            SanctumServiceProvider::class,
        ];
    }
}
