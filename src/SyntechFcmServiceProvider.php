<?php

namespace Syntech\Syntechfcm;

use Exception;
use Illuminate\Support\ServiceProvider;

class SyntechFcmServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(FCMService::class, function ($app) {
            return new FCMService();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/syntechfcm.php', 'fcm'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/syntechfcm.php' => config_path('syntechfcm.php'),
            ], 'config');
        }
    }
}
