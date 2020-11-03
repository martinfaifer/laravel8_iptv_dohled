<?php

/**
 * MODEL NAPOJENÝ CISTE NA IPTVDOKUMENTACI, KTERÁ PLANÍ POMOCÍ API DATA O STREAMECH, KDY SE MAJÍ NEBO NEMAJÍ DOHLEDOVAT
 *
 * V START_TIME SE VYPNE ZASILANÍ ALERTŮ
 * V END_TIME SE ZAPNE ZASÍLÁNÍ ALERTŮ
 *
 * V MEZI OBDOBÍ SE KANÁL DOHLEDUJE, ALE NEVYTVÁŘÍ SE ALERTING
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamSheduleFromIptvDoku extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'start_day',
        'start_time',
        'end_day',
        'end_time',
        'every_day'
    ];
}
