<?php

namespace App\Http\Controllers;

use App\Models\ChannelsWhichWaitingForNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChannelsWhichWaitingForNotificationController extends Controller
{
    /**
     * kontroler na získání informací o kanálech které se mají oznámit nebo už byli oznámene
     */

    /**
     * funkce na vrácení streamu, které cekají na notifikaci
     *
     * @return array
     */
    public static function return_streams_witch_waiting_for_notify(): array
    {
        // oznamují se kanály, které nefungují dele jak 5 min
        if (ChannelsWhichWaitingForNotification::where('notified', "false")->whereDate('updated_at', '>=', Carbon::now()->second(300))->first()) {
            foreach (ChannelsWhichWaitingForNotification::where('notified', "false")->whereDate('updated_at', '>=', Carbon::now()->second(300))->get() as $streamId) {
                $outup[] = $streamId['stream_id'];
            }
            return [
                'status' => "exist",
                'data' => $outup
            ];
        } else {

            return [
                'status' => "not_exist"
            ];
        }
    }

    /**
     * funkce vrátí streamy, které již byli oznámeny v mailu
     *
     * @return array
     */
    public static function return_streams_witch_are_notified(): array
    {
        if (ChannelsWhichWaitingForNotification::where('notified', "true")->first()) {
            foreach (ChannelsWhichWaitingForNotification::where('notified', "true")->get() as $streamId) {
                $outup[] = $streamId['stream_id'];
            }
            return [
                'status' => "exist",
                'data' => $outup
            ];
        } else {
            return [
                'status' => "not_exist"
            ];
        }
    }
}
