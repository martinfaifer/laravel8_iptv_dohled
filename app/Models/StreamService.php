<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamService extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'tsid',
        'pmtpid',
        'pcrpid',
        'provider',
        'name'
    ];
}
