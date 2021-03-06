<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Mail\SendErrorStream;
use App\Mail\SendStreamNotificationProblem;
use App\Mail\SendSuccessStream;
use App\Mail\SendSystemWarningAlert;
use App\Mail\SendUserNotificationWelcomeMessage;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\EmailNotification;
use App\Models\EmailStats;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PharIo\Manifest\Email;
use Illuminate\Support\Facades\Cache;
use App\Traits\NotificationTrait;
use App\Traits\SystemLogTrait;

class EmailNotificationController extends Controller
{
    use NotificationTrait;
    use SystemLogTrait;
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
    public static function notify_crashed_stream(): void
    {
        try {
            // zjistení zda je email, na který by se dalo odeslat data
            $checkIfExistAnyEmailToNotify = self::check_if_is_mail_to_notify("channels");

            // pokud status == exist , existuje email na který by se dal zaslat alert
            if ($checkIfExistAnyEmailToNotify['status'] === "exist") {

                Stream::where('sendMailAlert', true)->chunk(50, function ($streams) use ($checkIfExistAnyEmailToNotify) {
                    foreach ($streams as $stream) {
                        // vyhledání v cache, zda existuje záznam
                        if (Cache::has("stream" . $stream->id) && !Cache::has("stream" . $stream->id . "_sended_notification")) {
                            // stream je ve výpadku
                            foreach ($checkIfExistAnyEmailToNotify['data'] as $email) {
                                // odeslání mailu s parametry email, název kanálu
                                // odeslat do queue
                                Mail::to($email)->send(new SendErrorStream($stream->nazev, $stream->stream_url));
                                Cache::put("stream" . $stream->id . "_sended_notification", []);
                            }
                        }
                    }
                });
            }
        } catch (\Throwable $th) {
            self::create("send_emal_crashed_stream", $th);
        }
    }

    /**
     * odeslání notifikae,když stream je již funkcni
     *
     * @param string $streamId
     * @return void
     */
    public static function notify_success_stream(): void
    {
        try {
            // zjistení zda je email, na který by se dalo odeslat data
            $checkIfExistAnyEmailToNotify = self::check_if_is_mail_to_notify("channels");

            // pokud status == exist , existuje email na který by se dal zaslat alert
            if ($checkIfExistAnyEmailToNotify['status'] === "exist") {

                Stream::where('sendMailAlert', true)->chunk(50, function ($streams) use ($checkIfExistAnyEmailToNotify) {
                    foreach ($streams as $stream) {
                        // vyhledání v cache, zda existuje záznam
                        if (!Cache::has("stream" . $stream->id) && Cache::has("stream" . $stream->id . "_sended_notification")) {
                            // stream je ve výpadku
                            foreach ($checkIfExistAnyEmailToNotify['data'] as $email) {
                                // odeslání mailu s parametry email, název kanálu
                                // odeslat do queue
                                Mail::to($email)->send(new SendSuccessStream($stream->nazev));
                                Cache::pull("stream" . $stream->id . "_sended_notification");
                            }
                        }
                    }
                });
            }
        } catch (\Throwable $th) {
            self::create("send_emal_success_stream", $th);
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
     * @return array
     */
    public function return_emails(): array
    {
        if (!EmailNotification::first()) {
            return [];
        }
        foreach (EmailNotification::get() as $email) {
            $output[] = array(
                'id' => $email->id,
                'email' => $email->email,
                'channels' => $email->channels,
                'system' => $email->system,
                'channels_issues' => $email->channels_issues,
                'belongsTo' => User::where('id', $email->belongsTo)->first()->name
            );
        }

        return $output;
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
            return $this->frontend_notification("warning", "Tento email je již založen!");
        }

        // overeni ,ze je to emialova adredsa
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return $this->frontend_notification("warning", "Neplatný formát emailové adresy!");
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

            return $this->frontend_notification("success", "Založeno!");
        } catch (\Throwable $th) {
            return $this->frontend_notification("error", "NBepodařilo se založit!");
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
            return $this->frontend_notification("error", "Nepodařilo se vyhledat email ke smazání!");
        }


        try {
            EmailNotification::where('id', $request->emailId)->delete();
            return $this->frontend_notification("success", "Odebráno!");
        } catch (\Throwable $th) {
            return $this->frontend_notification("error", "Nepodařilo se odebrat!");
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
            return $this->frontend_notification("error", "Nepodařilo se vyhledat email ke smazání");
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
            return $this->frontend_notification("success", "Upraveno!");
        } catch (\Throwable $th) {
            return $this->frontend_notification("error", "Nepodařilo se upravit!");
        }
    }


    /**
     * fn pro vyhledaní emailových adress pagřících k nademu uzivateli
     *
     * @return array
     */
    public function return_emails_by_user(): array
    {
        $user = Auth::user();

        if (EmailNotification::where('belongsTo', $user->id)->first()) {

            return [
                'status' => "success",
                'data' => EmailNotification::where('belongsTo', $user->id)->get(['id', 'email', 'channels', 'channels_issues'])->toArray()
            ];
        } else {
            return [
                'status' => "error"
            ];
        }
    }
}
