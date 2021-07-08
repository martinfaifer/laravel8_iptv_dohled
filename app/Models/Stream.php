<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Stream extends Model
{
    use HasFactory;
    use Notifiable;

    public function routeNotificationForSlack($notification)
    {
        return Slack::first()->channel;
    }

    /**
     * ---------------------------------------------------------------------------------------------------------
     * STATUS MŮŽE OBSAHOVAT STAVY ( stop ,waiting (default), running , start_error )
     * ---------------------------------------------------------------------------------------------------------
     */

    protected $fillable = [
        'nazev',
        'stream_url',
        'image',
        'dokumentaceUrl',
        'dokumentaceId',
        'dohledovano',
        'dohledVidea',
        'dohledVolume',
        'dohledAudia',
        'dohledBitrate',
        'vytvaretNahled',
        'sendMailAlert',
        'sendSmsAlert',
        'status',
        'process_pid',
        'ffmpeg_pid',
        'socket_process_pid',
        'is_problem',
        'slack',
        'start_time'
    ];
}
