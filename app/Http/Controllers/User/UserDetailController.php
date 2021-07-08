<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Events\UserEdit;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationTrait;

class UserDetailController extends Controller
{
    use NotificationTrait;
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
            return $this->frontend_notification("success", "Upraveno!");
        } catch (\Throwable $th) {
            return $this->frontend_notification("error", "Neco se nepovedlo!");
        }
    }
}
