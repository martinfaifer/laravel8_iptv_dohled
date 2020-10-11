<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    /**
     * funkce na autorizaci uživatelů do systému
     *
     * @param Request $request
     * @return array
     */
    public function loginUser(Request $request): array
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            return [
                'isAlert' => "isAlert",
                'status' => "success",
                'msg' => "Úspěšně přihlášeno",
            ];
        } else {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nesprávné údaje!",
            ];
        }
    }
}
