<?php

namespace Mortogo321\LaravelNotify;

use Illuminate\Support\ServiceProvider;

class NotifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/notify.php',
            'notify'
        );

        $this->app->singleton('notify', function ($app) {
            return new NotifyManager($app['config']['notify']);
        });

        $this->app->alias('notify', NotifyManager::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/notify.php' => config_path('notify.php'),
            ], 'notify-config');
        }
    }
}
