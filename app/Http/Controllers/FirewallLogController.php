<?php

namespace App\Http\Controllers;

use App\Models\FirewallLog;
use Illuminate\Http\Request;

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
}
