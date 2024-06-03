<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

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
        RateLimiter::for('casinograph', function (object $job) {
            return Limit::perMinute(60);
        });
        RateLimiter::for('positiongraph', function (object $job) {
            return Limit::perMinute(60)->by(Auth::id());
        });
        RateLimiter::for('figuregraph', function (object $job) {
            return Limit::perMinute(60)->by(Auth::id());
        });
        RateLimiter::for('compoundfiguregraph', function (object $job) {
            return Limit::perMinute(60)->by(Auth::id());
        });
    }
}
