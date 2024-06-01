<?php

namespace  Syntech\Syntechfcm\Facades;

use Illuminate\Support\Facades\Facade;

class FCM extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fcm';
    }
}
