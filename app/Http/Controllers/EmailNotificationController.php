<?php

namespace App\Http\Controllers;

use App\Mail\SendErrorStream;
use App\Mail\SendSuccessStream;
use App\Mail\SendSystemWarningAlert;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\EmailNotification;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailNotificationController extends Controller
{

    /**
     * CONTROLER PRO NOTOFIKACE UŽIVATELÚM O PROBLÉMOVÝCH STREAMECH, PROBLÉMECH V SYSTÉMU ( SERVERU -> OVERLOADING A POD )
     */

    /**
     * funkce na zjistění, zda existuje jakýkoliv mail na prijem notifikací
     *
     * @param string $whatNotify ( channels , system )
     *
     * @return array
     */
    public static function check_if_is_mail_to_notify($whatNotify): array
    {

        if (!EmailNotification::first()) {
            return [
                'status' => "not_exist"
            ];
        }

        // pokud $whatNotify == "channels"
        if ($whatNotify == "channels") {
            if (!EmailNotification::where('channels', 'yes')->first()) {
                return [
                    'status' => "not_exist"
                ];
            } else {

                foreach (EmailNotification::where('channels', 'yes')->get() as $emailToNotifyChannels) {
                    $output[] = $emailToNotifyChannels['email'];
                }

                return [
                    'status' => "exist",
                    'data' => $output
                ];
            }
        }

        if ($whatNotify == "system") {
            // pokud $whatNotify == "system"
            if (!EmailNotification::where('system', 'yes')->first()) {
                return [
                    'status' => "not_exist"
                ];
            } else {

                foreach (EmailNotification::where('system', 'yes')->get() as $emailToNotifySystemData) {
                    $output[] = $emailToNotifySystemData['email'];
                }

                return [
                    'status' => "exist",
                    'data' => $output
                ];
            }
        }
    }

    /**
     * notifikace uživatelům informace o nefunkčních kanálech
     *
     * STATUS STREAMU JE ERROR
     * @return void
     */
    public static function notify_crashed_stream()
    {
        // zjistení zda je email, na který by se dalo odeslat data
        $checkIfExistAnyEmailToNotify = self::check_if_is_mail_to_notify("channels");

        // pokud status == exist , existuje email na který by se dal zaslat alert
        if ($checkIfExistAnyEmailToNotify['status'] == "exist") {
            // emaily ...

            // vyhkedání streamu, které jsou padlé ...
            $checkIfExistErrorStream = ChannelsWhichWaitingForNotificationController::return_streams_witch_waiting_for_notify();
            if ($checkIfExistErrorStream['status'] == "exist") {
                // id streamů ...
                foreach ($checkIfExistErrorStream['data'] as $streamId) {
                    // vyhledání názvu streamu dle id
                    $streamName = Stream::where('id', $streamId)->first()->nazev;

                    // odeslání mailu na všechny email adresy
                    foreach ($checkIfExistAnyEmailToNotify['data'] as $email) {

                        // odeslání mailu s parametry email, název kanálu
                        // odeslat do queue
                        Mail::to($email)->queue(new SendErrorStream($streamName));
                    }

                    // update záznamu ChannelsWhichWaitingForNotification -> notified => true
                    ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->update(['notified' => "true"]);
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param string $streamId
     * @return void
     */
    public static function notify_success_stream(string $streamId)
    {
        // zjistení zda je email, na který by se dalo odeslat data
        $checkIfExistAnyEmailToNotify = self::check_if_is_mail_to_notify("channels");

        // pokud status == exist , existuje email na který by se dal zaslat alert
        if ($checkIfExistAnyEmailToNotify['status'] == "exist") {
            // emaily ...

            // vyhledání streamu, podle $streamId
            if (ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->where('notified', "true")->first()) {
                $streamName = Stream::where('id', $streamId)->first()->nazev;
                // odeslání mailu na všechny email adresy
                foreach ($checkIfExistAnyEmailToNotify['data'] as $email) {

                    // odeslání mailu s parametry email, název kanálu
                    // odeslat do queue

                    Mail::to($email)->queue(new SendSuccessStream($streamName));

                    // odebrání záznamu z tabulky
                    ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->delete();
                }
            }
        }
    }


    /**
     * funkce na zaslílání alertů o problémech se systémem
     *
     * @param string $partOfSystem
     * @return void
     */
    public static function send_system_warning(string $partOfSystem): void
    {
        // zjistení zda je email, na který by se dalo odeslat data
        $checkIfExistAnyEmailToNotify = self::check_if_is_mail_to_notify("system");


        if ($checkIfExistAnyEmailToNotify['status'] == "exist") {
            // emaily ...


            // odeslání mailu na všechny email adresy
            foreach ($checkIfExistAnyEmailToNotify['data'] as $email) {

                // odeslat do queue
                Mail::to($email)->queue(new SendSystemWarningAlert($partOfSystem));
            }
        }
    }


    /**
     * funkce na vypsání všech imalových adres na které se budou zasílat alerty / pokud nic neexistuje vrácí pole se statusem empty
     *
     * @return void
     */
    public function return_emails()
    {
        if (!EmailNotification::first()) {
            return [
                'status' => "empty"
            ];
        }

        return EmailNotification::get();
    }
}
