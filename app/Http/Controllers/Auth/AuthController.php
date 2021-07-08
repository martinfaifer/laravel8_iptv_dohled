<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationTrait;

class AuthController extends Controller
{
    use NotificationTrait;

    /**
     * fn pro odhlášení uživatele z aplikace
     *
     * @return void
     */
    public function logout()
    {
        Auth::logout();
        return $this->frontend_notification("success", "Odhlášeno!");
    }

    /**
     * funkce na autorizaci uživatelů do systému
     *
     * @param Request $request
     * @return array
     */
    public function login(Request $request): array
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            if (Auth::user()->status === "access") {

                return $this->frontend_notification("success", "Přihlášeno!");
            } else {
                Auth::logout();
                return $this->frontend_notification("error", "Uživatel blokován!");
            }
        } else {
            return $this->frontend_notification("error", "Nesprávné údaje!");
        }
    }
}
