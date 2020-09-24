<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;


    /**
     * ---------------------------------------------------------------------------------------------------------
     * STATUS OBSAHUJE STAVY ( SUCCESS, WAITING, WARNING , NO_SCRAMBLED, OUT_OF_SYNC, PID_NOT_MATCH, ERROR )
     * ---------------------------------------------------------------------------------------------------------
     */

    protected $fillable = [
        'nazev',
        'stream_url',
        'image',
        'pcrPid',
        'dokumentaceUrl',
        'dokumentaceId',
        'dohledovano',
        'dohledVolume',
        'dohledBitrate',
        'vytvaretNahled',
        'sendMailAlert',
        'sendSmsAlert',
        'status',
        'process_pid'
    ];
}
