<?php

namespace App\Http\Controllers;

use App\Events\UserEdit;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDetailController extends Controller
{
    /**
     * funkce na editaci / pridani detailnich informací o uživateli
     *
     * @param Request $request->company $request->tel_number , $request->nickname
     * @return array
     */
    public function user_detail_edit(Request $request): array
    {

        $user = Auth::user();

        try {
            if (UserDetail::where('user_id', $user->id)->first()) {
                // jiz existuje zaznam
                UserDetail::find($user->id)->update(['company' => $request->company, 'tel_number' => $request->tel_number, 'nickname' => $request->nickname]);
            } else {

                // vytvari se novy
                UserDetail::create([
                    'user_id' => $user->id,
                    'company' => $request->company,
                    'tel_number' => $request->tel_number,
                    'nickname' => $request->nickname
                ]);
            }

            event(new UserEdit($user->id));
            return [
                'isAlert' => "isAlert",
                'status' => "success",
                'msg' => "Editace byla úspěšná"
            ];
        } catch (\Throwable $th) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nepodařilo se editovat"
            ];
        }
    }
}
