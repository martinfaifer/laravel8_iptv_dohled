<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationTrait;

class isAdmin
{
    use NotificationTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->role_id == 'admin') {
            return $next($request);
        } else {
            return $this->frontend_notification("error", "Nemáte dostatečné oprávnění!");
        }
    }
}
