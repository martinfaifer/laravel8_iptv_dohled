<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelsWhichWaitingForNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'notified',
    ];
}