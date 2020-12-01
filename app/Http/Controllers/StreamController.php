<?php

namespace App\Http\Controllers;

use App\Models\CcError;
use App\Models\Stream;
use App\Models\User;
use App\Models\StreamAlert;
use App\Models\StreamAudio;
use App\Models\StreamCa;
use App\Models\StreamHistory;
use App\Models\StreamService;
use App\Models\StreamVideo;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\StreamBitrate;
use App\Models\StreamNotificationLimit;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use App\Jobs\SendSuccessEmail;
use App\Models\StopedStream;
use Illuminate\Support\Facades\Auth;
use React\EventLoop\Factory;
use Illuminate\Support\Facades\Cache;

class StreamController extends Controller
{

    /**
     * update statusu u streamu
     *
     * @param string $streamId
     * @param string $status issue || success
     * @return void
     */
    public static function queue_diagnostic_update_stream_status(string $streamId, string $status): void
    {
        if ($status == "success") {
            Stream::where('id', $streamId)->update(["status" => $status]);

            // ZÁZNAM DO HISTORIE, ŽE STREAM JE OK
            StreamHistory::create([
                'stream_id' => $streamId,
                'status' => "stream_ok"
            ]);

            if (StreamAlert::where('stream_id',  $streamId)->first()) {
                foreach (StreamAlert::where('stream_id',  $streamId)->get() as $alertToDelete) {
                    StreamAlert::where('id', $alertToDelete["id"])->delete();
                }
            }

            if (ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->first()) {
                // odeslání mail notifikace pokud je zapotřebí
                dispatch(new SendSuccessEmail($streamId));
            }
        } else {
            Stream::where('id', $streamId)->update(["status" => $status]);
        }
    }

    /**
     * funkce na aktualizaci záznamu, vytvoření alertu, zaznamu do istorie a pod.
     * případně odebrání alertu
     *
     * @param [string] $streamId
     * @param [string] $streamSatus
     * @param [string] $message
     * @return void
     */
    public static function queue_diagnostic_update_status_and_create_more_information_about_strea($streamId, $arrData): void
    {
        foreach ($arrData as $streamAlertData) {
            // uložení pokud již neexistují

            if (!StreamAlert::where('stream_id', $streamId)->where('status', $streamAlertData['status'])->first()) {
                // ulození alertu
                StreamAlert::create([
                    'stream_id' => $streamId,
                    'status' => $streamAlertData['status'],
                    'message' => $streamAlertData['message']
                ]);

                // KONTROLA, ZDA EXISTUJI JIZ ZAZNAM V HISTORII
                if (StreamHistory::where('stream_id', $streamId)->orderBy('id', 'asc')->first()->status != $streamAlertData['status']) {
                    StreamHistory::create([
                        'stream_id' => $streamId,
                        'status' => $streamAlertData['status']
                    ]);
                } else {

                    StreamHistory::create([
                        'stream_id' => $streamId,
                        'status' => $streamAlertData['status']
                    ]);
                }
            }
        }
    }

    /**
     * funkce pro spuštění jednoho určitého streamu, který spustí admin ručně
     *
     * @param Request $request
     * @return array
     */
    public function start_diagnostic_stream(Request $request)
    {
        // some code here ...
        return [];
    }


    /**
     * funkce na overení a ulození pidu
     *
     * @param string $processPid
     * @param string $streamToStartId
     * @return void
     */
    public static function check_and_store_pid(string $postion, string $processPid, string $streamToStartId): void
    {

        // ne vždy bude existovat ffmpegPid, takže s ním se nebude počítat protože se dohleduje v samostatném procesu
        if (SystemController::check_if_process_running(intval($processPid)) == "running") {

            // uložení pidu do tabulky k streamu

            // pokud dorazil $processPid a ffmpegPid, uložení
            Stream::where('id', $streamToStartId)->update([$postion => intval($processPid)]);
            // vyhledání zda existuje záznam v tabulce stream_alerts se statusem start_error a stream_id streamu, co nyní spoustíme
            if (StreamAlert::where('stream_id', $streamToStartId)->where('status', "start_error")->first()) {
                // existuje záznam, dojde k jeho odebrání
                StreamAlert::where('stream_id', $streamToStartId)->where('status', "start_error")->delete();
            }
        } else {

            // sluzby nefungují jak by meli -> streamDiagnotika a tvorba náhledů
            // do streamu ulozime chybu a do streamAlertu vytvorime zaznam se statusem start_error

            Stream::where('id', $streamToStartId)->update(['status' => "start_error"]);

            StreamAlert::create([
                'stream_id' => $streamToStartId,
                'status' => "start_error",
                'msg' => "Nepodařilo se spustit stream"
            ]);

            // vyhledání, který pid nefunguje a killnutí pidu, který aktuálně funguje navíc, aby zbytečně nebral resourcess na serveru
            if (SystemController::check_if_process_running(intval($processPid)) == "running") {
                self::stop_diagnostic_stream_from_backend(intval($processPid));
            }

            // if (isset($ffmpegPid)) {
            //     if (SystemController::check_if_process_running(intval($ffmpegPid)) == "running") {
            //         self::stop_diagnostic_stream_from_backend(intval($ffmpegPid));
            //     }
            // }
        }
    }


    /**
     * funkce pro spustení vsech streamu, které se nediagnostikují ( chyby jim process_pid ==> null )
     *
     * @return void
     */
    public static function start_all_streams_for_diagnostic(): void
    {
        //vyhledání streamů, které mají process_pid == null , ffmpeg_pid == null a status má hodnoty jiné nez success , running nebo stop
        if (Stream::where('process_pid', null)->where('dohledovano', true)->where('status', "!=", 'stop')->first()) {
            // existuje minimálně jeden stream. který má process_pid == null a zároveň nemá status stop
            foreach (Stream::where('process_pid', null)->where('dohledovano', true)->where('status', "!=", 'stop')->get() as $streamToStart) {
                if (!StopedStream::where('streamId', "!=", $streamToStart->id)->first()) {

                    // otevreni socketu, pokud jiz nebyl otevren
                    $socketPid = shell_exec("nohup php artisan command:openStreamSocket " . $streamToStart->id . " " . null . " > /dev/null 2>&1 & echo $!; ");
                    self::check_and_store_pid("socket_process_pid", $socketPid, $streamToStart->id);

                    // pro každý stream spustí diagnostiku
                    // spustení příkazu a vrácení pidu
                    $processPid = shell_exec("nohup php artisan command:start_realtime_diagnostic_and_return_pid {$streamToStart->stream_url} {$streamToStart->id}" . ' > /dev/null 2>&1 & echo $!; ');

                    self::check_and_store_pid("process_pid", $processPid, $streamToStart->id);
                }
            }
        }
    }


    /**
     * funkce na killnutí procesu, který funguje na pozadí dle pidu
     *
     * @param int $pid
     * @return void
     */
    public static function stop_diagnostic_stream_from_backend($pid): void
    {
        shell_exec("kill {$pid}");
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
                shell_exec("kill {$stream->ffmpeg_pid}");
                // overení, ze pid skutecne neexistuje
                if (SystemController::check_if_process_running($stream->process_pid) == "not_running") {
                    if (SystemController::check_if_process_running($stream->ffmpeg_pid) == "not_running") {

                        // update zázanmu
                        Stream::where('id', $stream->id)->update(
                            [
                                'process_pid' => null,
                                'ffmpeg_pid' => null,
                                'status' => "stop"
                            ]
                        );

                        // vytvorení záznamu do stopstreams
                        StopedStream::create([
                            'streamId' => $request->streamId
                        ]);
                        // odeslání informace do frontendu
                        return [
                            'status' => "success",
                            'alert' => "Stream byl ukončen."
                        ];
                    }
                    // update zaznamu i kdyz se nepodarilo zastavit process
                    Stream::where('id', $stream->id)->update(
                        [
                            'process_pid' => null,
                            'status' => "issue"
                        ]
                    );
                    // odeslání informace do frontendu
                    return [
                        'status' => "warning",
                        'alert' => "Stream nebyl řádně ukončen!"
                    ];
                } else {
                    // odeslání informace do frontendu
                    return [
                        'status' => "error",
                        'alert' => "Stream se nepodařilo ukončit!"
                    ];
                }
                // uspesne ukonci a posle success response
            } catch (\Throwable $th) {
                // nepodari se ukoncit, kvuli nejake necekane chybe
                // zasle error response

                return [
                    'status' => "error",
                    'alert' => "Stream se nepodařilo ukončit!"
                ];
            }
        } else {
            // stream nebyl nalezen
            return [
                'status' => "error",
                'alert' => "Stream se nepodařilo ukončit, jelikož nebyl nalezen!"
            ];
        }
    }


    /**
     * funkce, která ověřuje, že všechny streamy, které mají u sebe uložené pidy funguje korektně
     *
     * pokud pid není nalezen ==> ukončení veškerých dalších processů, které souvysí se streamem
     *
     * @return void
     */
    public static function check_if_streams_running_corectly()
    {

        // Vyhledání zda funguje nějaký stream
        // vyhledají streamy, které nemají process_pid != null

        // event loop
        // $eventLoop = Factory::create();
        // $timer = $eventLoop->addPeriodicTimer(1, function () {
        if (Stream::where('process_pid', "!=", null)->where('status', "!=", "stop")->where('dohledovano', true)->first()) {   // => zde se berou vsechny streamy, které by se měli dohledovat respektive, které se dohledují
            foreach (Stream::where('process_pid', "!=", null)->where('dohledovano', true)->where('status', "!=", "stop")->get() as $streamWithProcessPid) {
                if (SystemController::check_if_process_running(intval($streamWithProcessPid->process_pid)) != "running") {
                    //
                    // ukocení streamu
                    self::stop_diagnostic_stream_from_backend(intval($streamWithProcessPid->process_pid));

                    if (!is_null($streamWithProcessPid->ffmpeg_pid)) {
                        self::stop_diagnostic_stream_from_backend(intval($streamWithProcessPid->ffmpeg_pid));
                    }

                    // spustení streamu
                    if (!StopedStream::where('streamId', $streamWithProcessPid->id)->first()) {
                        $processPid = shell_exec("nohup php artisan command:start_realtime_diagnostic_and_return_pid {$streamWithProcessPid->stream_url} {$streamWithProcessPid->id}" . ' > /dev/null 2>&1 & echo $!; ');
                        self::check_and_store_pid("process_pid", intval($processPid), $streamWithProcessPid->id);
                    }
                }
            }
        }
        // $eventLoop->run();
    }


    /**
     * funkce pro ukončení všech streamů co aktuálně fungují
     *
     * u každého streamu proběhne update process_pid => null ffmpeg_pid => null status => waiting , socket_process_pid => null
     *
     * @return array
     */
    public static function kill_all_running_streams()
    {

        // vyhledání zda existuje jakýkoliv stream pro ukoncení
        if (Stream::first()) {

            foreach (Stream::get() as $streamProUkonceni) {
                //  ukončení ffmpegu, pokud aktuálně funguje
                if (!is_null($streamProUkonceni->ffmpeg_pid)) {
                    self::stop_diagnostic_stream_from_backend($streamProUkonceni->ffmpeg_pid);
                }
                if (!is_null($streamProUkonceni->process_pid)) {
                    self::stop_diagnostic_stream_from_backend($streamProUkonceni->process_pid);
                }

                if (!is_null($streamProUkonceni->socket_process_pid)) {
                    self::stop_diagnostic_stream_from_backend($streamProUkonceni->socket_process_pid);
                }

                Stream::where('id', $streamProUkonceni->id)
                    ->update(
                        [
                            'status' => "waiting",
                            'process_pid' => null,
                            'ffmpeg_pid' => null,
                            'socket_process_pid' => null
                        ]
                    );
            }

            return [
                'status' => "success",
                'message' => "Všechny streamy byli ukončeny!"
            ];
        } else {
            return [
                'status' => "info",
                'message' => "Neexistuje žádný stream!"
            ];
        }
    }



    /**
     * ------------------------------------------------------------------------------------------------------------
     *
     * BLOK URČENÝ PRO VÝPIS INFORMACÍ O STREAMECH VE FRONTENDU
     *
     * TÝKÁ SE TO CELKOVÝCH INFORMACÍ O JEDNOTLIVÝCH STREAMECH, ALERTECH A DALŠÍCH DETAILNÍCH INFORMACÍ
     *
     * ------------------------------------------------------------------------------------------------------------
     */


    /**
     * funkce pro výpis streamů, ktere mají status jiný než success nebo waiting
     * vyhledání jednotlivých detailních informací a vypsání ( pouze pokud se jedná o status issue u statusu error, se toto netýká jelikož stream nejspíše vůbec nefunguje)
     *
     * error => error color
     * issue => warning color
     * not_scrambled => warning color
     *
     * barvy jsou brány dle sass
     *
     * @return array
     */
    public static function show_problematic_streams_as_alerts()
    {
        if (!Stream::where('status', "error")->orWhere('status', "issue")->where('dohledovano', true)->first()) {
            // neexistuje žádný stream, který má jinou hodnotu než success nebo waiting

            // vrací prázdné pole
            return [];
        }

        // zpracování problematických streamů
        foreach (Stream::where('status', "error")->orWhere('status', "issue")->where('dohledovano', true)->get() as $problematicStream) {
            $dataAboutStream[] = self::sort_stream_status_by_data($problematicStream);
        }

        return response()->json($dataAboutStream);
    }


    /**
     * funkce pro prebrání chybových statusu streamu a následně vytvorení polí, které obsahují informace o chybách
     *
     * @param array $stream
     * @return array
     */
    public static function sort_stream_status_by_data($stream)
    {
        switch ($stream['status']) {
            case "error":
                // error status

                return [
                    'status' => "error",
                    'msg' => "{$stream["nazev"]} nefunguje!"
                ];
                break;
            case "not_scrambled":
                // not_scrambled status
                return [
                    'status' => "warning",
                    'msg' => "{$stream["nazev"]} se nedekryptuje!"
                ];

                break;
            case "issue":
                // issue status
                return [
                    'status' => "warning",
                    'msg' => "{$stream["nazev"]} má problémy!",
                    'data' => StreamAlertController::return_information_about_issued_stream($stream["id"])
                ];
                break;
            default:
                // neznámí status
                return [
                    'status' => "error",
                    'msg' => "Neznámí status streamu"
                ];
        }
    }


    /**
     * funkce, která vrátí kanály v mozaice
     *
     * @param Request $request
     * @return array
     */
    public function streams_for_mozaiku(Request $request)
    {

        $user = Auth::user();
        return Stream::where('dohledovano', true)->orderBy('nazev', 'asc')->paginate($user->pagination, ['id', 'image', 'nazev', 'status']);
    }


    /**
     * funkce, která získá odkaz na náhled, status kanálu
     *
     * @param Request $request->streamId
     * @return void
     */
    public function stream_info_image(Request $request)
    {
        if ($stream = Stream::where('id', $request->streamId)->first()) {

            // stream s id $request->streamId existuje
            return [
                'nazev' => $stream['nazev'],
                'status' => $stream['status'],
                'image' => $stream["image"]
            ];
        } else {

            redirect(404);
        }
    }

    /**
     * funkce na vrácení status streamu
     *
     * @param Request $request
     * @return array
     */
    public static function stream_info_checkStatus(Request $request): array
    {
        return [
            'status' => Stream::where('id', $request->streamId)->first()->status
        ];
    }


    /**
     * funkce na získání detailních informací o streamu z Cache
     *
     * @param Request $request->streamId
     * @return array
     */
    public function stream_info_detail(Request $request): array
    {

        $video = null;
        $audio = null;
        $ca = null;

        // vyhledání záznamu z cache pro video
        if (Cache::has($request->streamId . "_video")) {
            $video = Cache::get($request->streamId . "_video");
        }

        // cache pro audio
        if (Cache::has($request->streamId . "_audio")) {
            $audio = Cache::get($request->streamId . "_audio");
        }

        // cache pro ca
        if (Cache::has($request->streamId . "_ca")) {
            $ca = Cache::get($request->streamId . "_ca");
        }

        return [
            'audio' => $audio,
            'video' => $video,
            'ca' => $ca
        ];

        // overení zda existuje stream s tímto id
        // if (Stream::where('id', $request->streamId)->first()) {

        //     return [
        //         'audio' => StreamAudio::where('stream_id', $request->streamId)->first() ?? null,
        //         'video' => StreamVideo::where('stream_id', $request->streamId)->first() ?? null,
        //         'service' => StreamService::where('stream_id', $request->streamId)->first() ?? null,
        //         'ca' => StreamCa::where('stream_id', $request->streamId)->first() ?? null,
        //     ];
        // } else {
        //     redirect(404);
        // }
    }

    /**
     * funkce na získání informací z dokumentace pomocí api
     * stream musí mít vyplněnou uri v poli dokumentaceUrl
     *
     * pokud stream nemá tuto informaci vyplněnou, vrátí se "none" string
     *
     * pokud stream neexistuje redirect 404
     *
     * @param Request $request
     * @return array | redirect
     */
    public function stream_info_doku(Request $request)
    {
        // vyhledání zda existuje streamId
        if ($stream = Stream::where('id', $request->streamId)->first()) {

            if (is_null($stream['dokumentaceUrl'])) {
                return [
                    'status' => "none",
                    'message' => "Stream nemá povolené API"
                ];
            }

            // zavolání funkce z RemoteApiController
            return;
        } else {

            return [
                'status' => "none",
                'message' => "Stream neexistuje"
            ];
        }
    }


    /**
     * funkce na získání procent funknčích kanálů
     *
     * @return string
     */
    public function percent_working_streams(): string
    {

        $allStreams = Stream::all()->count(); // 100%


        $workingStreams = Stream::where('status', "success")->orWhere('status', "issue")->get()->count();

        return round(($workingStreams * 100) / $allStreams);
    }


    /**
     * funkce na vypsání všecj informací o streamu
     *
     * @return void
     */
    public function get_streams()
    {
        return Stream::get();
    }



    /**
     * Undocumented function
     *
     * @param Request $request (nazev, stream_url, dohledovano, dohledVolume, vytvaretNahled, sendMailAlert, sendSmsAlert , video_discontinuities, audio_discontinuities, audio_scrambled, streamIssues)
     * @return array
     */
    public function edit_stream(Request $request): array
    {

        // overení vstupu ...
        if (empty($request->nazev) || empty($request->stream_url)) {
            return [
                'isAlert' => "isAlert",
                'status' => "warning",
                'msg' => "Chybí vyplnit data!"
            ];
        }
        if ($request->dohledovano) {
            $status = "waiting";
            // odebrání z tabulky stopedStreams
            StopedStream::where('streamId', $request->streamId)->delete();
        } else {
            $status = "stop";
            $streamData = Stream::where('id', $request->sreamId)->first();
            // kill vsech procesů co běží na pozadí u streamu
            self::stop_diagnostic_stream_from_backend($streamData->process_pid);
            self::stop_diagnostic_stream_from_backend($streamData->socket_process_pid);

            // založení do stopedStreams
            StopedStream::create([
                'streamId' => $request->streamId
            ]);
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
                // neexituje záznam, vytocorime
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


        Stream::where('id', $request->streamId)->update([
            'nazev' => $request->nazev,
            'stream_url' => $request->stream_url,
            'dohledovano' => $request->dohledovano ?? 0,
            'dohledVolume' => $request->dohledVolume ?? 0,
            'vytvaretNahled' => $request->vytvaretNahled ?? 0,
            'sendMailAlert' => $request->sendMailAlert ?? 0,
            'sendSmsAlert' => $request->sendSmsAlert ?? 0,
            'status' => $status
        ]);

        return [
            'isAlert' => "isAlert",
            'status' => "success",
            'msg' => "Stream byl editován!"
        ];
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
        $stream = Stream::where('id', $request->streamId)->first();

        // killnuti procesu pro diagnostiku
        if (!is_null($stream->process_pid) || !is_null($stream->socket_process_pid)) {
            self::stop_diagnostic_stream_from_backend($stream->process_pid);
            self::stop_diagnostic_stream_from_backend($stream->socket_process_pid);
        }


        // smazaní streamu z db
        Stream::where('id', $request->streamId)->delete();

        try {
            // funkce na odebrání veškerách informací o streamu, bez jakéhokoliv returnu
            self::delete_all_stream_information($request->streamId);
        } catch (\Throwable $th) {
            // nemela by vzniknout žádná chyba
        }


        // unlink náhledu pokud není hodnota "image" = "false"
        if ($stream->image != "false") {
            if (file_exists(public_path($stream->image))) {
                // Náhled existuje => odebrání náhledu z filesystemu
                unlink(public_path($stream->image));
            }
        }

        return [
            'isAlert' => "isAlert",
            'status' => "success",
            'msg' => "Stream byl odebrán!"
        ];
    }

    /**
     * funknce na odebrnání veškerých informací o streamu, vyvolaná akcí "delete" od uživatele
     *
     * @param string $streamId
     * @return void
     */
    public static function delete_all_stream_information(string $streamId): void
    {
        // vyhledání v historii a smazání
        if (StreamHistory::where('stream_id', $streamId)->first()) {
            foreach (StreamHistory::where('stream_id', $streamId)->get() as $dataForDelete) {
                StreamHistory::where('id', $dataForDelete['id'])->delete();
            }
        }

        // Odebrání dat z Alertu
        if (StreamAlert::where('stream_id', $streamId)->first()) {
            foreach (StreamAlert::where('stream_id', $streamId)->get() as $alertDataForDelete) {
                StreamAlert::where('id', $alertDataForDelete['id'])->delete();
            }
        }

        // vyhledání zda jsou data ve fronte na alerting
        if (ChannelsWhichWaitingForNotification::where('id', $streamId)->first()) {
            foreach (ChannelsWhichWaitingForNotification::where('id', $streamId)->get() as $waitingDataForDelete) {
                ChannelsWhichWaitingForNotification::where('id', $waitingDataForDelete['id'])->delete();
            }
        }

        // vyhledání dza existuje záznam v limitacích
        if (StreamNotificationLimit::where('stream_id', $streamId)->first()) {
            StreamNotificationLimitController::delete_stream_limit_for_notification($streamId);
        }

        // odebrání záznamu z tabulky stream_audio
        if (StreamAudio::where('stream_id', $streamId)->first()) {
            StreamAudio::where('stream_id', $streamId)->delete();
        }

        // obebrání zíznamu z tabulky stream_cas
        if (StreamCa::where('stream_id', $streamId)->first()) {
            StreamCa::where('stream_id', $streamId)->delete();
        }

        // oberání z tabulky stream_services
        if (StreamService::where('stream_id', $streamId)->first()) {
            StreamService::where('stream_id', $streamId)->delete();
        }

        // oberání z tabulky stream_videos
        if (StreamVideo::where('stream_id', $streamId)->first()) {
            StreamVideo::where('stream_id', $streamId)->delete();
        }

        // odebrání veškerých záznamů z tabulky cc_errors
        if (CcError::where('streamId', $streamId)->first()) {
            foreach (CcError::where('streamId', $streamId)->get() as $ccr) {
                CcError::where('id', $ccr['id'])->delete();
            }
        }
    }


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
        // overení, že není empty $request->streamUrl a $request->stream_nazev
        if (empty($request->streamUrl) || empty($request->stream_nazev)) {
            return [
                'isAlert' => "isAlert",
                'status' => "warning",
                'msg' => "Není vše řádně vyplněno"
            ];
        }

        if (Stream::where('stream_url', $request->streamUrl)->first()) {
            return [
                'isAlert' => "isAlert",
                'status' => "warning",
                'msg' => "Tato URL se již dohleduje"
            ];
        }

        try {
            Stream::create([
                'nazev' => $request->stream_nazev,
                'stream_url' => $request->streamUrl,
                'status' => "waiting",
                'dohledovano' => $request->dohled,
                'dohledVolume' => $request->audioDohled,
                'vytvaretNahled' => $request->vytvareniNahledu,
                'sendMailAlert' => $request->emailAlert,
                'sendSmsAlert' => $request->smsAlert
            ]);

            if ($request->streamIssues) {
                // opetovné vyhledání streamu, pro ziskání id , pro případné uložení limitu pro notifikaci
                $streamId = Stream::where('stream_url', $request->streamUrl)->first()->id;
                // založení
                StreamNotificationLimitController::add_stream_to_notification_limit(
                    $streamId,
                    $request->video_discontinuities,
                    $request->audio_discontinuities,
                    $request->audio_scrambled
                );
            }

            return [
                'isAlert' => "isAlert",
                'status' => "success",
                'msg' => "Úspěšně založeno"
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
     * funkce na vyhledání informací o streamech a vypsání do polí a následně zaslat do dokumentace ci jineho externího systému
     *
     * @param Request $request
     * @return array
     */
    public static function get_information_about_streams(Request $request): array
    {

        // 'multicastUri' => $multicastUri,
        //         'h264Uri' => $h264Uri,
        //         'h265Uri' => $h265Uri,

        // vyhledání informací o streamech, pokud je multicast, h264 nebo h265 null, vyzvirí se empty field

        if ($multicast = Stream::where('stream_url', $request->multicastUri)->first()) {
            $audioMulticast = StreamAudio::where('stream_id', $multicast->id)->first();
            $videoMulticast = StreamVideo::where('stream_id', $multicast->id)->first();
            $multicastData = [
                'img' => env("APP_URL") . "/" . $multicast->image,
                'name' => $multicast->nazev . " multicast",
                'audioLang' => $audioMulticast->language,
                'audioDiscontinuities' => $audioMulticast->discontinuities,
                'audioScrambled' => $audioMulticast->scrambled,
                'videoDiscontinuities' => $videoMulticast->discontinuities,
                'videoScrambled' => $videoMulticast->scrambled,
                'streamStatus' => $multicast->status,
                'history' => StreamHistoryController::stream_info_history_ten_for_events($multicast->id)
            ];
        } else {
            $multicastData = [];
        }

        if ($h264 = Stream::where('stream_url', $request->h264Uri)->first()) {
            $audioH264 = StreamAudio::where('stream_id', $h264->id)->first();
            $videoH264 = StreamVideo::where('stream_id', $h264->id)->first();
            $h264Data = [
                'img' => env("APP_URL") . "/" . $h264->image,
                'name' => $h264->nazev . " H264",
                'audioLang' => $audioH264->language,
                'audioDiscontinuities' => $audioH264->discontinuities,
                'audioScrambled' => $audioH264->scrambled,
                'videoDiscontinuities' => $videoH264->discontinuities,
                'videoScrambled' => $videoH264->scrambled,
                'streamStatus' => $h264->status,
                'history' => StreamHistoryController::stream_info_history_ten_for_events($h264->id)
            ];
        } else {
            $h264Data = [];
        }

        if ($h265 = Stream::where('stream_url', $request->h265Uri)->first()) {
            $audioH265 = StreamAudio::where('stream_id', $h265->id)->first();
            $videoH265 = StreamVideo::where('stream_id', $h265->id)->first();
            $h265Data = [
                'img' => env("APP_URL") . "/" . $h265->image,
                'name' => $h265->nazev . " H265",
                'audioLang' => $audioH265->language,
                'audioDiscontinuities' => $audioH265->discontinuities,
                'audioScrambled' => $audioH265->scrambled,
                'videoDiscontinuities' => $videoH265->discontinuities,
                'videoScrambled' => $videoH265->scrambled,
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
     * fn pro prihlášení multicástů per stream
     *
     * pokud stream nabyde statusu stop => ukocení
     *
     *
     *
     * @param string $streamId
     * @return void
     */
    public static function hold_multicast_per_stream(string $streamId, $kill = null): void
    {
        // event loop per stream
        $eventLoop = Factory::create();

        // loop, kdy se kazdou "s" testuje ctení
        $eventLoop->addPeriodicTimer(1, function () use ($streamId, $kill) {

            $stream = Stream::where('id', $streamId)->first();

            // rozlození url na uri a port

            $url = explode(":", $stream->stream_url);

            $socket = socket_create(AF_INET, SOCK_RAW, SOL_UDP);
            $result = socket_connect($socket, $url[0], $url[1]);
            if ($result === false) {

                socket_close($socket);
            } else {
                socket_close($socket);
                $socket = socket_create(AF_INET, SOCK_RAW, SOL_UDP);
                socket_bind($socket, $url[0], $url[1]);

                $buf = "";

                socket_recv($socket, $buf, 2048, MSG_WAITALL);
            }


            // ukončení event loopu

            if (!is_null($kill)) {
                // odpojení od socketu
                socket_close($socket);
            }
        });

        $eventLoop->run();
    }


    /**
     * fn pro získání názvu a stavu o dohledování dle streamId
     *
     * @param Request $request->streamId
     * @return array
     */
    public function get_stream_name_and_dohled(Request $request): array
    {
        if (!Stream::where('id', $request->streamId)->first()) {
            return [
                'status' => "error",
            ];
        }

        $stream = Stream::where('id', $request->streamId)->first();
        return [
            'status' => "success",
            'stream_name' => $stream->nazev,
            'dohledovano' => $stream->dohledovano
        ];
    }


    /**
     * fn pro zakladní editaci nazvu a stavu dohledování kanálu
     *
     * @param Request $request->streamId, streamName, dohledovano
     * @return array
     */
    public function mozaika_stream_small_edit(Request $request): array
    {
        if (!Stream::where('id', $request->streamId)->first()) {
            return [
                'status' => "error",
            ];
        }

        // update zaznamu
        if (empty($request->streamName)) {
            return [
                'isAlert' => "isAlert",
                'status' => "warning",
                'msg' => "Chybí vyplnit název!"
            ];
        }


        Stream::where('id', $request->streamId)->update([
            'nazev' => $request->streamName,
        ]);

        return [
            'isAlert' => "isAlert",
            'status' => "success",
            'msg' => "Stream byl editován!"
        ];
    }
}
