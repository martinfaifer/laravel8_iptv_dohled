<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CcError extends Model
{
    use HasFactory;

    protected $fillable = [
        'streamId',
        'ccError',
        'pozition',
        'expirace',
    ];
}
