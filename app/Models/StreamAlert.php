<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamAlert extends Model
{
    use HasFactory;

    /**
     *
     *
     * ------------------------------------------------------------------------------------
     * STATUS NESE MOŽNOSTI AUDIO_WARNING, VIDEO_WARNING, CA_WARNING , START_ERROR
     * MESSAGE -> ZPRÁVA DO FRONTENDU
     * ------------------------------------------------------------------------------------
     *
     * @var array
     */
    protected $fillable = [
        'stream_id',
        'status',
        'message'
    ];
}
