<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;


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
        'socket_process_pid'
    ];
}
