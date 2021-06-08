<?php

namespace App\Traits;

trait NotificationTrait
{
    public static function frontend_notification(string $alertStatus = "error", string $message = "Error 500"): array
    {
        return [
            'status' => $alertStatus,
            'alert' => array(
                'status' => $alertStatus,
                'msg' => $message
            )
        ];
    }


    public static function send_error500_report($th)
    {
    }
}
