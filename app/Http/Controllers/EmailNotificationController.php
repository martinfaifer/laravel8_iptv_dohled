<?php

namespace App\Http\Controllers;

use App\Mail\SendErrorStream;
use App\Mail\SendStreamNotificationProblem;
use App\Mail\SendSuccessStream;
use App\Mail\SendSystemWarningAlert;
use App\Mail\SendUserNotificationWelcomeMessage;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\EmailNotification;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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


        if ($whatNotify == "channels_issues") {
            // pokud $whatNotify == "channels_issues"
            if (!EmailNotification::where('channels_issues', 'yes')->first()) {
                return [
                    'status' => "not_exist"
                ];
            } else {

                foreach (EmailNotification::where('channels_issues', 'yes')->get() as $emailToNotifySystemData) {
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
                    $url = env("APP_URL") . "/#/stream/{$streamId}";
                    // odeslání mailu na všechny email adresy
                    foreach ($checkIfExistAnyEmailToNotify['data'] as $email) {

                        // odeslání mailu s parametry email, název kanálu
                        // odeslat do queue
                        Mail::to($email)->queue(new SendErrorStream($streamName, $url));
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
            // podmínka pro odeslání alertu, je notified == true
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
     * funkce na odeslání uvítacího mailu, pro nového uzivatele
     *
     * @param [type] $email
     * @param [type] $password
     * @param [type] $url
     * @return void
     */
    public static function send_welcome_message_to_new_user($email, $password, $url): void
    {
        Mail::to($email)->queue(new SendUserNotificationWelcomeMessage($email, $password, $url));
    }

    /**
     * funkce na odeslání alertu o problémovém streamu
     *
     * @param [type] $subject
     * @param [type] $streamId
     * @param [type] $streamName
     * @param [type] $url
     * @return void
     */
    public static function send_information_about_problem_stream($subject, $streamId, $streamName, $url): void
    {
        // get emails to send data
        $checkIfExistAnyEmailToNotify = self::check_if_is_mail_to_notify("channels_issues");

        if ($checkIfExistAnyEmailToNotify['status'] == "exist") {
            // emaily ...


            // odeslání mailu na všechny email adresy
            foreach ($checkIfExistAnyEmailToNotify['data'] as $email) {

                // odeslat do queue
                Mail::to($email)->queue(new SendStreamNotificationProblem($subject, $streamId, $streamName, $url));
            }
        }
    }


    /**
     * funkce na vypsání všech emailových adres na které se budou zasílat alerty / pokud nic neexistuje vrácí pole se statusem empty
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

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return array
     */
    public function create_email(Request $request): array
    {
        $user = Auth::user();
        if (EmailNotification::where('email', $request->email)->first()) {
            return [
                'isAlert' => "isAlert",
                'status' => "warning",
                'msg' => "Tento email je již založen"
            ];
        }


        // overeni ,ze je to emialova adredsa
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return [
                'isAlert' => "isAlert",
                'status' => "warning",
                'msg' => "Neplatný formát emailové adresy"
            ];
        }

        if ($request->streamAlerts == false) {
            $streamAlert = "no";
        } else {
            $streamAlert = "yes";
        }
        if ($request->systemAlerts == false) {
            $systemAlerts = "no";
        } else {
            $systemAlerts = "yes";
        }
        if ($request->streamAlertsIssue == false) {
            $channelIssue = "no";
        } else {
            $channelIssue = "yes";
        }


        try {
            EmailNotification::create([
                'email' => $request->email,
                'belongsTo' => $user->id,
                'channels' => $streamAlert,
                'system' => $systemAlerts,
                'channels_issues' => $channelIssue
            ]);

            return [
                'isAlert' => "isAlert",
                'status' => "success",
                'msg' => "Založeno"
            ];
        } catch (\Throwable $th) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nepodařilo se založit"
            ];
        }
    }


    /**
     * Undocumented function
     *
     * @param Request $request->emailId
     * @return array
     */
    public function delete_email(Request $request): array
    {
        if (!EmailNotification::where('id', $request->emailId)->first()) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nepodařilo se vyhledat email ke smazání"
            ];
        }


        try {
            EmailNotification::where('id', $request->emailId)->delete();
            return [
                'isAlert' => "isAlert",
                'status' => "success",
                'msg' => "Email byl odebrán"
            ];
        } catch (\Throwable $th) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nepodařilo se odebrat email"
            ];
        }
    }

    /**
     * funkce na editaci emalové adresy
     *
     * @param Request $request
     * @return array
     */
    public function edit_email(Request $request): array
    {
        if (!EmailNotification::where('id', $request->emailId)->first()) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nepodařilo se vyhledat email ke smazání"
            ];
        }

        if ($request->streamAlerts == false) {
            $streamAlert = "no";
        } else {
            $streamAlert = "yes";
        }
        if ($request->systemAlerts == false) {
            $systemAlerts = "no";
        } else {
            $systemAlerts = "yes";
        }

        if ($request->streamAlertsIssue == false) {
            $channelIssue = "no";
        } else {
            $channelIssue = "yes";
        }

        try {
            EmailNotification::where('id', $request->emailId)->update(['channels' => $streamAlert, 'channels_issues' => $channelIssue, 'system' => $systemAlerts]);

            return [
                'isAlert' => "isAlert",
                'status' => "success",
                'msg' => "Editováno"
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
