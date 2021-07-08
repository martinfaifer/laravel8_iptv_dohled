<?php

namespace App\Http\Controllers\Firewall;

use App\Http\Controllers\Controller;
use App\Models\FirewallLog;

class FirewallLogController extends Controller
{

    /**
     * funkce na vypsání logu, kde se uchovávávají neúspěšné pokusy o připojení z neznámích adress
     *
     * @return void
     */
    public static function get_logs()
    {
        if (!FirewallLog::first()) {
            return [
                'status' => "empty"
            ];
        }

        return FirewallLog::get();
    }

    /**
     * automatické odmazávání logů z firewallu, které jsou starší než 24 hodin
     *
     * @return void
     */
    public static function prum_ips_older_than_twentyfour_hours(): void
    {
        if (FirewallLog::where('created_at', '<=', now()->subHours(24))->first()) {
            FirewallLog::where('created_at', '<=', now()->subHours(24))->delete();
        }
    }
}
