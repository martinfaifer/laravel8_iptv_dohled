<?php

namespace App\Http\Middleware;

use App\Http\Controllers\FirewallController;
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
        if (FirewallController::check_if_is_ip_allowed($_SERVER['REMOTE_ADDR']) == "ok") {
            return $next($request);
        } else {

            redirect(null, 404);
        }
    }
}
