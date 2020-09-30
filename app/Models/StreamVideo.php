<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamVideo extends Model
{
    use HasFactory;

    /**
     *
     *
     *
     */

    protected $fillable = [
        'stream_id',
        'pid',
        'bitrate',
        'access',
        'discontinuities',
        'scrambled'
    ];
}
