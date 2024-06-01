<?php

namespace Syntech\Syntechfcm;

use Exception;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use Syntech\Syntechfcm\Channels\FcmChannel;

class SyntechFcmServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('fcm', function ($app) {
            return new FcmService();
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

        $this->app->make(ChannelManager::class)->extend('fcm', function ($app) {
            return new FcmChannel();
        });
    }
}
