<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'status'
    ];
}
