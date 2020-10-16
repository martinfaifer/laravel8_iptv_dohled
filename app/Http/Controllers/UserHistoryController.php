<?php

namespace App\Http\Controllers;

use App\Models\UserHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserHistoryController extends Controller
{
    public function user_history()
    {

        $user = Auth::user();

        if (UserHistory::where('user_id', $user->id)->first()) {

            return UserHistory::where('user_id', $user->id)->get();
        }

        return "none";
    }
}
