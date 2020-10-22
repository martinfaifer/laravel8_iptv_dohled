<?php

namespace App\Http\Controllers;

use App\Events\UserEdit;
use App\Models\Stream;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            if (Auth::user()->status == "active") {
                return [
                    'isAlert' => "isAlert",
                    'status' => "success",
                    'msg' => "Úspěšně přihlášeno",
                ];
            } else {
                Auth::logout();
                return [
                    'isAlert' => "isAlert",
                    'status' => "error",
                    'msg' => "Uživatel je zablokován!",
                ];
            }
        } else {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nesprávné údaje!",
            ];
        }
    }


    /**
     * fn pro odhlášení uživatele z aplikace
     *
     * @return void
     */
    public function logout()
    {
        Auth::logout();
        return [
            'isAlert' => "isAlert",
            'status' => "success",
            'msg' => "Odhlášeno!",
        ];
    }


    /**
     * funkce na získání informací o přihlášeném uživateli
     *
     * @return array
     */
    public function getLoggedUser()
    {
        $user = Auth::user();
        if (empty($user)) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nejste přihlášen!",
            ];
        } else {

            if ($user->mozaika == "default") {
                $mozaika = $user->mozaika;
                $customData = $user->customData;
            } else {
                $mozaika = $user->mozaika;

                // customData obsahuje id statických streamů

                // $streamsId = explode(",", $user->customData);
                $streamsId = json_decode($user->customData, true);
                // ['id', 'image', 'nazev', 'status']

                foreach ($streamsId as $streamId) {
                    $streamData = Stream::where('id', $streamId)->first();
                    $customData[] = array(
                        'id' => $streamId,
                        'status' => $streamData->status,
                        'image' => $streamData->image,
                        'nazev' => $streamData->nazev,
                    );
                }
            }

            return [
                'name' => $user->name,
                'role_id' => $user->role_id,
                'mozaika' => $mozaika,
                'customData' => $customData
            ];
        }
    }


    /**
     * detailní informace o uživately
     *
     * @return array
     */
    public static function userDetail(): array
    {
        $user = Auth::user();
        if (empty($user)) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nejste přihlášen!",
            ];
        } else {

            if (!is_null($user->customData)) {
                // $customData = explode(",", $user->customData);


                // ['id', 'image', 'nazev', 'status']
                $customData = json_decode($user->customData, true);
                foreach ($customData as $streamId) {
                    $streamData = Stream::where('id', $streamId)->first();
                    $staticChannels[] = array(
                        'nazev' => $streamData->nazev,
                        'id' => $streamData->id
                    );
                }
            } else {
                $customData = $user->customData;
            }

            // vyhledání user role
            $role = UserRole::where('id', $user->role_id)->first();

            $userDetail = UserDetail::where('user_id', $user->id)->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'userDetail' => $userDetail ?? "false",
                'mozaika' => $user->mozaika,
                'customData' => $customData,
                'staticChannels' => $staticChannels ?? "false",
                'role' => $role->role_name,
                'inicial' => $user->name[0],
                'pagination' => $user->pagination,
                'created_at' => substr($user->created_at, 0, 10)
            ];
        }
    }


    /**
     * funkce na zaslání všech streamů, pro navolení do statické mozaiky
     *
     * @return void
     */
    public function user_streams()
    {
        foreach (Stream::get() as $stream) {
            $output[] = array(
                'id' => $stream->id,
                'nazev' => $stream->nazev
            );
        }

        return $output;
    }


    /**
     * funkce na editaci ( pridání / odebrání ) statických kanálů
     *
     * @param Request $request->staticChannelsData
     * @return void
     */
    public function user_streams_set(Request $request)
    {

        return $request->staticChannelsData;
    }


    /**
     * funkce na editaci jmena a hesla
     *
     * @param Request $request->name , $request->email
     * @return array
     */
    public function obecne_edit(Request $request): array
    {

        $user = Auth::user();

        try {
            User::where('id', $user->id)->update(['name' => $request->name, 'email' => $request->email]);


            event(new UserEdit($user->id));
            return [
                'isAlert' => "isAlert",
                'status' => "success",
                'msg' => "Úspěšně z editováno"
            ];
        } catch (\Throwable $th) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nepodařilo se editovat"
            ];
        }
    }

    /**
     * funkce na editaci prihlaseneho uzivatele
     *
     * @param Request $request->password , $request->passwordCheck
     * @return array
     */
    public function user_password_edit(Request $request): array
    {

        $user = Auth::user();


        try {
            User::where('id', $user->id)->update(['password' => Hash::make($request->password)]);
            event(new UserEdit($user->id));
            return [
                'isAlert' => "isAlert",
                'status' => "success",
                'msg' => "Heslo úspěšně změměno"
            ];
        } catch (\Throwable $th) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nepodařilo se editovat"
            ];
        }
    }

    /**
     * funkce na uoravu zobrazení mozaiky
     *
     * @param Request $request->pagination, $request->customMozaika, $request->staticChannels
     * @return void
     */
    public function user_gui_edit(Request $request)
    {

        // ovření vstupů
        if ($request->customMozaika == true) {
            $mozaika = "custom";
            if (!empty($request->staticChannels)) {
                $customData = json_encode($request->staticChannels, true);
            } else {
                // status , msg
                return [
                    "status" => array(
                        'status' => "issue",
                        'msg' => "Je nutné vybrat kanály, které mají být statické!"
                    )
                ];
            }
        } else {
            $mozaika = "default";
            $customData = null;
        }

        // uložení k uživateli
        try {
            $user = Auth::user();
            User::where('id', $user->id)->update(['mozaika' => $mozaika, 'customData' => $customData, 'pagination' => $request->pagination]);
            return [
                "status" => array(
                    'status' => "success",
                    'msg' => "Editace byla úspěšná!"
                ),
                "data" => Auth::user()
            ];
        } catch (\Throwable $th) {
            return [
                "status" => array(
                    'status' => "error",
                    'msg' => "Nepodařilo se editovat!"
                )
            ];
        }
    }

    /**
     * funkce na výpis všech uuživatelů
     *
     * @return array
     */
    public function users(): array
    {

        if (!Auth::user()) {
            return [
                'status' => "User not logged"
            ];
        }
        foreach (User::get() as $user) {

            if (!is_null($user->customData)) {
                $staticChannels = array();
                $customData = json_decode($user->customData, true);
                foreach ($customData as $streamId) {
                    $streamData = Stream::where('id', $streamId)->first();
                    $staticChannels[] = $streamData->nazev;
                }
                $staticChannels = implode(",", $staticChannels);
            }

            $output[] = array(
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => UserRole::where('id', $user->role_id)->first()->role_name,
                'mozaika' => $user->mozaika,
                'customData' => $staticChannels ?? null,
                'pagination' => $user->pagination,
                'status' => $user->status
            );
            unset($staticChannels);
        }
        return $output;
    }
}
