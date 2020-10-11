<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemoteApiConnection extends Model
{
    use HasFactory;


    protected $fillable = [
        'popis',
        'url',
        'api_klic',
        'request_type',
        'klic'
    ];
}
