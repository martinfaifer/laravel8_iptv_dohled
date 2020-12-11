<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'value_type' // ram, system load (avarage 5min), swap, hdd, streams ( počet funkčních / nefunkčních )
    ];

    // vse az na system load test kazdou 1 min
}
