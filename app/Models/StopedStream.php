<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StopedStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'streamId'
    ];
}
