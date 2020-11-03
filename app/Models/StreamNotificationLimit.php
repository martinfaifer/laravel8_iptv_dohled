<?php

/**
 * MODEL NA PRIDÁNÍ LIMITŮ PRO ALERTING PROBLÉMU VE STREAMU
 *
 * NEMUSÍ EXISTOVAT U STREAMU
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamNotificationLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'video_bitrate',
        'video_discontinuities',
        'video_scrambled',
        'audio_discontinuities',
        'audio_scrambled'
    ];
}
