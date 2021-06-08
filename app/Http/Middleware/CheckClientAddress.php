<?php

namespace App\Http\Middleware;

use App\Http\Controllers\FirewallController;
use App\Models\FirewallLog;
use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;

class CheckClientAddress
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // overení, ze je firewall aktivní
        // if (SystemSetting::where('modul', "firewall")->where('stav', "aktivni")->first()) {
        //     if (FirewallController::check_if_is_ip_allowed($_SERVER['REMOTE_ADDR']) == "ok") {

        //         return $next($request);
        //     } else {

        //         // uložení do logu
        //         FirewallLog::create([
        //             'ip' => $_SERVER['REMOTE_ADDR']
        //         ]);

        //         return abort(404);
        //     }
        // } else {
        //     return $next($request);
        // }
        return $next($request);
    }
}
