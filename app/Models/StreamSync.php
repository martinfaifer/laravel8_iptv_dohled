<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamSync extends Model
{
    use HasFactory;



    protected $fillable = [
        'stream_id',
        'sync_data'
    ];
}
