<?php

namespace Jurager\Tracker\Macros;

use Illuminate\Support\Facades\Route;

class RouteMacros
{
    /**
     * Get the routes for the Laravel Auth Tracker.
     *
     * @return \Closure
     */
    public function authTracker()
    {
        return function ($prefix) {

            Route::prefix($prefix)->group(function () {

                // Route to manage logins
                Route::get('/', 'Auth\TrackingController@listLogins')->name('login.list');

                // Logout routes
                Route::middleware('auth')->group(function () {
                    Route::post('logout/all', 'Auth\TrackingController@logoutAll')->name('logout.all');
                    Route::post('logout/others', 'Auth\TrackingController@logoutOthers')->name('logout.others');
                    Route::post('logout/{id}', 'Auth\TrackingController@logoutById')->where('id', '[0-9]+')->name('logout.id');
                });
            });
        };
    }
}
