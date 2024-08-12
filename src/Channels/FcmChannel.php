<?php

namespace  Syntech\Syntechfcm\Channels;

use Illuminate\Notifications\Notification;
use Syntech\Syntechfcm\Facades\Fcm;

class FcmChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $message = $notification->toFcm($notifiable);

        Fcm::send($message);
    }
}
