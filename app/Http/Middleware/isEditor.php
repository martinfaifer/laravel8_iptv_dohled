<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Traits\NotificationTrait;

class isEditor
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
        // $user->role_id == 'editor' 
        $user = Auth::user();
        if (!$user) {
            return $this->frontend_notification("error", "Nemáte dostatečné oprávnění!");
        }
        if ($user->role_id == 'editor' || $user->role_id == 'admin') {
            return $next($request);
        }
    }
}
