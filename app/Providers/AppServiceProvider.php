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
    // Fix for Vercel Read-Only Filesystem
    if (env('APP_ENV') === 'production') {
        $path = '/tmp/storage/framework/';
        config([
            'view.compiled' => $path . 'views',
            'cache.stores.file.path' => $path . 'cache',
            'session.files' => $path . 'sessions',
        ]);

        if (!is_dir($path . 'views')) {
            mkdir($path . 'views', 0755, true);
        }
    }
}
}
