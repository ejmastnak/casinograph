<?php

namespace App\Providers;

use App\Extensions\MyFileSessionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Session::extend('myfile', function (Application $app) {
            $files = $app['files'];
            $path = $app['config']->get('session.files');
            $lifetime = $app['config']->get('session.lifetime');
            return new MyFileSessionHandler($files, $path, $lifetime);
        });
    }
}
