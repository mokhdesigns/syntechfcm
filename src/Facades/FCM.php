<?php

namespace  Syntech\SyntechFcm\Facades;

use Illuminate\Support\Facades\Facade;

class FCM extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fcm';
    }
}