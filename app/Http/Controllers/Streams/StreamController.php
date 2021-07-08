<?php

namespace App\Http\Controllers\Streams;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Diagnostic\StreamDiagnosticController;
use App\Http\Controllers\Streams\StreamHistoryController;
use App\Http\Controllers\Notifications\StreamNotificationLimitController;
use App\Http\Controllers\System\SystemController;
use App\Models\CcError;
use App\Models\Stream;
use App\Models\StreamAlert;
use App\Models\StreamHistory;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\StreamNotificationLimit;
use Illuminate\Http\Request;
use App\Models\StopedStream;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Traits\NotificationTrait;
use App\Traits\CacheTrait;
use App\Traits\TSDuckTrait;
use Illuminate\Support\Facades\Validator;

class StreamController extends Controller
{
    use TSDuckTrait;
    use NotificationTrait;
    use CacheTrait;

    /**
     * funkce na vypsání všecj informací o streamu
     *
     */
    public function index()
    {
        return Stream::all();
    }

    /**
     * funkce, která vrátí kanály v mozaice
     *
     * @param Request $request
     * @return array
     */
    public function streams_for_mozaiku()
    {
        $user = Auth::user();

        if ($user) {
            return Stream::where('dohledovano', true)->where('status', 'running')->where('is_problem', false)->orderBy('nazev', 'asc')->paginate($user->pagination, ['id', 'image', 'nazev', 'is_problem']);
        }

        return [];
    }

    // public function error_streams_for_mozaika(): array
    // {
    //     if (!Stream::where([['dohledovano', true], ['is_problem', true]])->first()) {
    //         return [
    //             'status' => "empty"
    //         ];
    //     }

    //     return [
    //         'status' => "success",
    //         'data' => Stream::where([['dohledovano', true], ['is_problem', true]])->orderBy('nazev', 'asc')->get(['id', 'image', 'nazev', 'is_problem'])
    //     ];
    // }


    // /**
    //  * funkce na získání procent funknčích kanálů
    //  *
    //  * @return string
    //  */
    // public function percent_working_streams(): string
    // {
    //     $allStreams = Stream::all()->count(); // 100%

    //     $workingStreams = Stream::where('status', "success")->orWhere('status', "issue")->get()->count();

    //     return round(($workingStreams * 100) / $allStreams);
    // }

    /**
     * funkce na vyhledání informací o streamech a vypsání do polí a následně zaslat do dokumentace ci jineho externího systému
     *
     * @param Request $request
     * @return array
     */
    public static function get_information_about_streams(Request $request): array
    {
        // vyhledání informací o streamech, pokud je multicast, h264 nebo h265 null, vyzvirí se empty field
        if ($multicast = Stream::where('stream_url', $request->multicastUri)->first()) {
            $multicastData = [
                'img' => env("APP_URL") . "/" . $multicast->image,
                'name' => $multicast->nazev . " multicast",
                'streamStatus' => $multicast->status,
                'history' => StreamHistoryController::stream_info_history_ten_for_events($multicast->id)
            ];
        } else {
            $multicastData = [];
        }

        if ($h264 = Stream::where('stream_url', $request->h264Uri)->first()) {
            $h264Data = [
                'img' => env("APP_URL") . "/" . $h264->image,
                'name' => $h264->nazev . " H264",
                'streamStatus' => $h264->status,
                'history' => StreamHistoryController::stream_info_history_ten_for_events($h264->id)
            ];
        } else {
            $h264Data = [];
        }

        if ($h265 = Stream::where('stream_url', $request->h265Uri)->first()) {
            $h265Data = [
                'img' => env("APP_URL") . "/" . $h265->image,
                'name' => $h265->nazev . " H265",
                'streamStatus' => $h265->status,
                'history' => StreamHistoryController::stream_info_history_ten_for_events($h265->id)
            ];
        } else {
            $h265Data = [];
        }

        if (empty($multicastData) && empty($h264Data) && empty($h265Data)) {
            return [
                'status' => "fail"
            ];
        }

        return [
            'status' => "success",
            'streamData' => [
                $multicastData, $h264Data, $h265Data

            ]
        ];
    }

    /**
     * fn pro získání názvu a stavu o dohledování dle streamId
     *
     * @param Request $request->streamId
     * @return array
     */
    public function get_stream_name_and_dohled(Request $request): array
    {
        if (!$stream = Stream::where('id', $request->streamId)->first()) {
            return [
                'status' => "error",
            ];
        }

        return [
            'status' => "success",
            'stream_name' => $stream->nazev,
            'dohledovano' => $stream->dohledovano
        ];
    }

    /**
     * fn pro výpočet funkčních || negunkčních streamů
     *
     * @return void
     */
    public static function take_count_of_working_issued_stopped_streams(): void
    {
        if (Stream::first()) {

            Cache::put('running_streams' . date('H:i'), [
                'value' => Stream::where([['is_problem', false], ['dohledovano', true]])->count(),
                'created_at' => date('H:i')
            ], now()->addMinutes(240));

            Cache::put('issueing_streams' . date('H:i'), [
                'value' => Stream::where([['is_problem', true], ['dohledovano', true]])->count(),
                'created_at' => date('H:i')
            ], now()->addMinutes(240));

            Cache::put('stopped_streams' . date('H:i'), [
                'value' => Stream::where([['dohledovano', false]])->count(),
                'created_at' => date('H:i')
            ], now()->addMinutes(240));
        }
    }

    /**
     * fn pro vykreslení areachartu pro dashboard
     *
     * @return array
     */
    public function retun_count_of_working_streams(): array
    {
        for ($i = 240; $i > 1; $i--) {
            if (Cache::has('running_streams' . now()->subMinutes($i)->format('H:i'))) {
                $cache = Cache::get('running_streams' . now()->subMinutes($i)->format('H:i'));

                $seriesData[] = $cache['value'];
                $xaxis[] = $cache['created_at'];
            }

            // stopped
            if (Cache::has('stopped_streams' . now()->subMinutes($i)->format('H:i'))) {
                $cache_stopped = Cache::get('stopped_streams' . now()->subMinutes($i)->format('H:i'));

                $seriesData_stopped[] = $cache_stopped['value'];
            }

            // issued
            if (Cache::has('issueing_streams' . now()->subMinutes($i)->format('H:i'))) {
                $cache_issue = Cache::get('issueing_streams' . now()->subMinutes($i)->format('H:i'));

                $seriesData_issued[] = $cache_issue['value'];
            }
        }
        if (isset($xaxis)) {

            $output = [
                array(
                    'name' => "funkční streamy",
                    'data' => $seriesData
                ),
                array(
                    'name' => "zastavené streamy",
                    'data' => $seriesData_stopped
                ),
                array(
                    'name' => "problémové streamy",
                    'data' => $seriesData_issued
                )
            ];

            return [
                'status' => "exist",
                'xaxis' => $xaxis,
                'seriesData' => $output
            ];
        }

        return [
            'status' => "empty"
        ];
    }

    /**
     * fn pro vykreslení donut chartu
     *
     * @return array
     */
    public function return_streams_data_for_donutChart(): array
    {
        if (!Stream::first()) {
            return [
                'status' => "empty"
            ];
        }
        $pocet = array();
        $pocetCekajicich = Stream::where('status', "waiting")->count();
        $pocetFunkcnich = Stream::where([['status', "running"], ['is_problem', false]])->count();
        $pocetStopnutych = Stream::where('status', "stop")->count();
        $pocetNefunkcnich = Stream::where([['is_problem', true], ['status', "running"]])->count();

        array_push($pocet, $pocetCekajicich, $pocetFunkcnich, $pocetStopnutych, $pocetNefunkcnich);
        // statusy
        // waiting, success, issue, stop

        // count jednotlivých statusů
        return [
            'status' => "success",
            'seriesDonut' => $pocet,
            'chartOptionsDonut' => array(
                'labels' => ['čekající', 'funkční', 'stopnuté', 'nefunkční']
            )
        ];
    }

    // public function get_last_ten(): array
    // {
    //     if (Stream::first()) {
    //         return Stream::orderByDesc('id')->take(10)->get('nazev')->toArray();
    //     } else {
    //         return [];
    //     }
    // }

    public static function get_information_about_streams_by_streamId(Request $request): array
    {
        if (is_null($request->streamId) || empty($request->streamId)) {
            return [
                'status' => "fail"
            ];
        }
        if ($stream = Stream::where('id', $request->streamId)->first()) {
            return [
                'status' => "success",
                'streamData' => [
                    'img' => env("APP_URL") . "/" . $stream->image,
                    'name' => $stream->nazev . " multicast",
                    'streamStatus' => $stream->status,
                    'streamId' => $request->streamId,
                    'history' => StreamHistoryController::stream_info_history_ten_for_events($stream->id)
                ]
            ];
        }

        return [
            'status' => "fail"
        ];
    }


    // ---------------------------------  -----------------------------------------------

    /**
     * statická funkce na založení nového stremu pro dohled
     *
     * Slouží i jako přístup po API, kdy dokumentace, zasle req pro založení
     *
     * @param Request $request
     * @return array
     */
    public static function create_stream(Request $request): array
    {

        $validation = Validator::make($request->all(), [
            'streamUrl' => ['required'],
            'stream_nazev' => ['required'],
        ]);

        if ($validation->fails()) {
            return self::frontend_notification('error', 'Nebylo vše vyplněno!');
        }

        // otestování streamu
        if (isset($request->isDohled)) {
            // napojení na tsduck a report
            $analyza = self::analyze($request->streamUrl);
            if (!str_contains($analyza, 'pid:')) {
                return self::frontend_notification("error", "Nepodařila se analýza streamu, stream nebyl přidán!");
            }
        }

        if (Stream::where('stream_url', $request->streamUrl)->first()) {
            self::frontend_notification("warning", "URL již existuje");
        }

        try {
            $stream = Stream::create([
                'nazev' => $request->stream_nazev,
                'stream_url' => $request->streamUrl,
                'status' => "waiting",
                'dohledovano' => $request->dohled,
                'dohledAudia' => $request->dohledAudia,
                'vytvaretNahled' => $request->vytvareniNahledu,
                'sendMailAlert' => $request->emailAlert,
                'sendSmsAlert' => $request->smsAlert
            ]);

            if ($request->streamIssues) {
                // založení
                StreamNotificationLimitController::add_stream_to_notification_limit(
                    $stream->id,
                    $request->video_discontinuities,
                    $request->audio_discontinuities,
                    $request->audio_scrambled
                );
            }

            return self::frontend_notification("success", "Vytvořeno!");
        } catch (\Throwable $th) {
            return self::frontend_notification("error", "Nepodařilo se založit!");
        }
    }


    // /**
    //  * update statusu u streamu
    //  *
    //  * @param string $streamId
    //  * @param string $status issue || success
    //  * @return void
    //  */
    // public static function queue_diagnostic_update_stream_status(object $stream, string $status): void
    // {
    //     if ($status != "success") {
    //         $stream->update([
    //             'status' => $status
    //         ]);
    //         exit();
    //     }

    //     $stream->update([
    //         'status' => $status
    //     ]);

    //     if (StreamHistory::where([['stream_id', $stream->id], ['status', '!=', 'stream_ok']])->latest()->first()) {
    //         // ZÁZNAM DO HISTORIE, ŽE STREAM JE OK
    //         StreamHistory::create([
    //             'stream_id' => $stream->id,
    //             'status' => "stream_ok"
    //         ]);
    //     }

    //     StreamAlert::where('stream_id',  $stream->id)->each(function ($alertToDelete) {
    //         StreamAlert::where('id', $alertToDelete["id"])->delete();
    //     });

    //     if (ChannelsWhichWaitingForNotification::where('stream_id', $stream->id)->first()) {
    //         // odeslání mail notifikace pokud je zapotřebí
    //         dispatch(new SendSuccessEmail($stream->id));
    //     }
    // }

    /**
     * Undocumented function
     *
     * @param Request $request (nazev, stream_url, dohledovano, dohledVolume, vytvaretNahled, sendMailAlert, sendSmsAlert , video_discontinuities, audio_discontinuities, audio_scrambled, streamIssues)
     * @return array
     */
    public function edit_stream(Request $request): array
    {
        $status = null;

        // overení vstupu ...
        if (empty($request->nazev) || empty($request->stream_url)) {
            return $this->frontend_notification("warning", "Chybí vyplnit data!");
        }
        $stream_to_edit = Stream::find($request->streamId);


        if ($request->dohledovano == true) {
            // Zjistení puvodního statusu, pokud je jiný než stop, nemění se
            if ($stream_to_edit->status === "stop") {
                $status = "waiting";
            }
        } else {
            $status = "stop";

            // kill vsech procesů co běží na pozadí u streamu
            if (!is_null($stream_to_edit->process_pid)) {
                $stream_to_edit->update([
                    'image' => "false",
                    'start_time' => null
                ]);
                StreamDiagnosticController::stop_diagnostic_stream_from_backend($stream_to_edit->process_pid);
                // smazani vsech alertů

                StreamHistory::create([
                    'stream_id' => $request->streamId,
                    'status' => "stream_stoped_by_user"
                ]);
            }
        }
        if ($request->streamIssues) {
            // vyhledání zda jiz existuje záznam
            if (StreamNotificationLimit::where('stream_id', $request->streamId)->first()) {
                // existuje, muzeme aktualizovat záznam
                StreamNotificationLimitController::update_stream_limit_for_notification(
                    $request->streamId,
                    $request->video_discontinuities,
                    $request->audio_discontinuities,
                    $request->audio_scrambled
                );
            } else {
                // neexistuje záznam
                StreamNotificationLimitController::add_stream_to_notification_limit(
                    $request->streamId,
                    $request->video_discontinuities,
                    $request->audio_discontinuities,
                    $request->audio_scrambled
                );
            }
        } else {
            // odebereme zaznam z tabulky
            StreamNotificationLimitController::delete_stream_limit_for_notification($request->streamId);
        }

        if (!is_null($status)) {
            $stream_to_edit->update([
                'nazev' => $request->nazev,
                'stream_url' => $request->stream_url,
                'dohledovano' => $request->dohledovano ?? 0,
                'dohledAudia' => $request->dohledAudia ?? 0,
                'vytvaretNahled' => $request->vytvaretNahled ?? 0,
                'sendMailAlert' => $request->sendMailAlert ?? 0,
                'sendSmsAlert' => $request->sendSmsAlert ?? 0,
                'status' => $status
            ]);

            return $this->frontend_notification("success", "Upraveno!");
        }

        $stream_to_edit->update([
            'nazev' => $request->nazev,
            'stream_url' => $request->stream_url,
            'dohledovano' => $request->dohledovano ?? 0,
            'dohledAudia' => $request->dohledAudia ?? 0,
            'vytvaretNahled' => $request->vytvaretNahled ?? 0,
            'sendMailAlert' => $request->sendMailAlert ?? 0,
            'sendSmsAlert' => $request->sendSmsAlert ?? 0
        ]);

        return $this->frontend_notification("success", "Upraveno!");
    }


    /**
     * funkce na smazaní streamu ze systemu
     *
     * @param Request $request
     * @return array
     */
    public static function delete_stream(Request $request)
    {
        // vyhledání streamu
        $stream = Stream::find($request->streamId);

        if (!$stream) {
            return self::frontend_notification("error", "Nenalezen stream");
        }

        // killnuti procesu pro diagnostiku
        if (!is_null($stream->process_pid)) {
            StreamDiagnosticController::stop_diagnostic_stream_from_backend($stream->process_pid);
        }

        $stream->delete();

        try {
            // funkce na odebrání veškerách informací o streamu, bez jakéhokoliv returnu
            self::delete_all_stream_information($request->streamId);
        } catch (\Throwable $th) {
            // nemela by vzniknout žádná chyba
            return self::frontend_notification("success", "Odebráno!");
        }

        // unlink náhledu pokud není hodnota "image" = "false"
        if ($stream->image != "false") {
            if (file_exists(public_path($stream->image))) {
                // Náhled existuje => odebrání náhledu z filesystemu
                unlink(public_path($stream->image));
            }
        }

        return self::frontend_notification("success", "Odebráno!");
    }

    /**
     * funknce na odebrnání veškerých informací o streamu, vyvolaná akcí "delete" od uživatele
     *
     * @param string $streamId
     * @return void
     */
    protected static function delete_all_stream_information(string $streamId): void
    {
        // vyhledání v historii a smazání
        if (StreamHistory::where('stream_id', $streamId)->first()) {
            StreamHistory::where('stream_id', $streamId)->get()->each(function ($dataForDelete) {
                StreamHistory::where('id', $dataForDelete['id'])->delete();
            });
        }

        // Odebrání dat z Alertu
        if (StreamAlert::where('stream_id', $streamId)->first()) {
            StreamAlert::where('stream_id', $streamId)->get()->each(function ($alertDataForDelete) {
                StreamAlert::where('id', $alertDataForDelete['id'])->delete();
            });
        }

        // vyhledání zda jsou data ve fronte na alerting
        if (ChannelsWhichWaitingForNotification::where('id', $streamId)->first()) {
            ChannelsWhichWaitingForNotification::where('id', $streamId)->get()->each(function ($waitingDataForDelete) {
                ChannelsWhichWaitingForNotification::where('id', $waitingDataForDelete['id'])->delete();
            });
        }

        // vyhledání dza existuje záznam v limitacích
        if (StreamNotificationLimit::where('stream_id', $streamId)->first()) {
            StreamNotificationLimitController::delete_stream_limit_for_notification($streamId);
        }

        // odebrání veškerých záznamů z tabulky cc_errors
        if (CcError::where('streamId', $streamId)->first()) {

            CcError::where('streamId', $streamId)->get()->each(function ($ccr) {
                CcError::where('id', $ccr['id'])->delete();
            });
        }
    }

    /**
     * fn pro zakladní editaci nazvu a stavu dohledování kanálu
     *
     * @param Request $request->streamId, streamName, dohledovano
     * @return array
     */
    public function mozaika_stream_small_edit(Request $request): array
    {
        if (!$stream = Stream::where('id', $request->streamId)->first()) {
            return $this->frontend_notification("error", "Chyba!");
        }
        // update zaznamu
        if (empty($request->streamName)) {
            return $this->frontend_notification("warning", "Chybí název!");
        }

        $stream->update([
            'nazev' => $request->streamName,
        ]);

        return $this->frontend_notification("success", "Upraveno!");
    }


    /**
     * funkce pro zastavení diagnostiky streamu
     *
     * @param Request $request->streamId
     * @return array
     */
    public static function stop_diagnostic_stream_from_frontend(Request $request)
    {
        // posle se kill a cislo pidu, který je uložen
        // vyhledání císla process_pid u streamu
        if ($stream = Stream::where('id', $request->streamId)->first()) {

            try {
                // kill $stream->process_pid
                shell_exec("kill {$stream->process_pid}");
                // overení, ze pid skutecne neexistuje

                if (SystemController::check_if_process_running($stream->process_pid) === "not_running") {

                    // update zázanmu
                    $stream->update([
                        'process_pid' => null,
                        'ffmpeg_pid' => null,
                        'status' => "stop"
                    ]);

                    // vytvorení záznamu do stopstreams
                    StopedStream::create([
                        'streamId' => $request->streamId
                    ]);
                    // odeslání informace do frontendu
                    self::frontend_notification("success", 'Stream byl ukončen!');
                } else {
                    // odeslání informace do frontendu
                    return self::frontend_notification("error", "Stream se neodařilo ukončit!");
                }
                // uspesne ukonci a posle success response
            } catch (\Throwable $th) {
                // nepodari se ukoncit, kvuli nejake necekane chybe
                return self::frontend_notification("error", "Stream se neodařilo ukončit!");
            }
        } else {
            // stream nebyl nalezen
            return self::frontend_notification("error", "Stream nenalezen!");
        }
    }
}
