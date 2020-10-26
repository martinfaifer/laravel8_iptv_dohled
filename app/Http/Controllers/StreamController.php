<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamAlert;
use App\Models\StreamAudio;
use App\Models\StreamCa;
use App\Models\StreamHistory;
use App\Models\StreamService;
use App\Models\StreamVideo;
use App\Models\ChannelsWhichWaitingForNotification;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use React\EventLoop\Factory;

class StreamController extends Controller
{


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
    public static function check_and_store_pid(string $processPid, string $streamToStartId): void
    {

        // ne vždy bude existovat ffmpegPid, takže s ním se nebude počítat protože se dohleduje v samostatném procesu
        if (SystemController::check_if_process_running(intval($processPid)) == "running") {

            // uložení pidu do tabulky k streamu

            // pokud dorazil $processPid a ffmpegPid, uložení
            Stream::where('id', $streamToStartId)->update(['process_pid' => intval($processPid), 'ffmpeg_pid' => $ffmpegPid ?? null]);
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
            sleep(6);
            if (SystemController::check_if_process_running(intval($processPid)) == "running") {
                self::stop_diagnostic_stream_from_backend(intval($processPid));
            }

            if (isset($ffmpegPid)) {
                if (SystemController::check_if_process_running(intval($ffmpegPid)) == "running") {
                    self::stop_diagnostic_stream_from_backend(intval($ffmpegPid));
                }
            }
        }
    }


    /**
     * funkce pro spustení vsech streamu, které se nediagnostikují ( chyby jim process_pid ==> null )
     *
     * @return void
     */
    public static function start_all_streams_for_diagnostic()
    {
        //vyhledání streamů, které mají process_pid == null , ffmpeg_pid == null a status má hodnoty jiné nez success , running nebo stop
        if (Stream::where('process_pid', null)->where('dohledovano', true)->where('status', "!=", 'stop')->first()) {
            // existuje minimálně jeden stream. který má process_pid == null a zároveň nemá status stop
            foreach (Stream::where('process_pid', null)->where('dohledovano', true)->where('status', "!=", 'stop')->get() as $streamToStart) {

                // pro každý stream spustí diagnostiku
                // spustení příkazu a vrácení pidu
                $processPid = shell_exec("nohup php artisan command:start_realtime_diagnostic_and_return_pid {$streamToStart->stream_url} {$streamToStart->id}" . ' > /dev/null 2>&1 & echo $!; ');

                self::check_and_store_pid($processPid, $streamToStart->id);
            }
        }
    }


    /**
     * funkce na killnutí procesu, který funguje na pozadí dle pidu
     *
     * @param int $pid
     * @return void
     */
    public static function stop_diagnostic_stream_from_backend($pid)
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
        // $timer = $eventLoop->addPeriodicTimer(5, function () {
        if (Stream::where('process_pid', "!=", null)->where('dohledovano', true)->first()) {
            foreach (Stream::where('process_pid', "!=", null)->where('dohledovano', true)->get() as $streamWithProcessPid) {
                if (SystemController::check_if_process_running(intval($streamWithProcessPid->process_pid)) != "running") {
                    // ukocení streamu

                    self::stop_diagnostic_stream_from_backend(intval($streamWithProcessPid->process_pid));
                    if (!is_null($streamWithProcessPid->ffmpeg_pid)) {
                        self::stop_diagnostic_stream_from_backend(intval($streamWithProcessPid->ffmpeg_pid));
                    }

                    // spustení streamu


                    $processPid = shell_exec("nohup php artisan command:start_realtime_diagnostic_and_return_pid {$streamWithProcessPid->stream_url} {$streamWithProcessPid->id}" . ' > /dev/null 2>&1 & echo $!; ');
                    self::check_and_store_pid(intval($processPid), $streamWithProcessPid->id);
                }
            }
        }
        // $eventLoop->run();
    }


    /**
     * funkce pro ukončení všech streamů co aktuálně fungují
     *
     * u každého streamu proběhne update process_pid = null ffmpeg_pid = null status = waiting
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

                Stream::where('id', $streamProUkonceni->id)->update(['status' => "waiting", 'process_pid' => null, 'ffmpeg_pid' => null]);
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
     * funkce na získání detailních informací o streamu
     *
     * @param Request $request->streamId
     * @return array | redirect
     */
    public function stream_info_detail(Request $request)
    {
        // overení zda existuje stream s tímto id
        if (Stream::where('id', $request->streamId)->first()) {

            return [
                'audio' => StreamAudio::where('stream_id', $request->streamId)->first() ?? null,
                'video' => StreamVideo::where('stream_id', $request->streamId)->first() ?? null,
                'service' => StreamService::where('stream_id', $request->streamId)->first() ?? null,
                'ca' => StreamCa::where('stream_id', $request->streamId)->first() ?? null,
            ];
        } else {
            redirect(404);
        }
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
     * @param Request $request (nazev, stream_url, dohledovano, dohledVolume, vytvaretNahled, sendMailAlert, sendSmsAlert)
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
            $status = "success";
        } else {
            $status = "stop";
        }
        // self::stop_diagnostic_stream_from_backend(intval($request->streamId));

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
        // vyhledání streamu a
        $stream = Stream::where('id', $request->streamId)->first();
        $streamPid = $stream->process_pid;
        // killnuti procesu pro diagnostiku
        $killPidu = self::stop_diagnostic_stream_from_frontend($request);
        // smazaní streamu z db
        Stream::where('id', $request->streamId)->delete();
        // vyhledání v historii a smazání
        if (StreamHistory::where('stream_id', $request->streamId)->first()) {
            foreach (StreamHistory::where('stream_id', $request->streamId)->get() as $dataForDelete) {
                StreamHistory::where('id', $dataForDelete['id'])->delete();
            }
        }

        // Odebrání dat z Alertu
        if (StreamAlert::where('stream_id', $request->streamID)->first()) {
            foreach (StreamAlert::where('stream_id', $request->streamID)->get() as $alertDataForDelete) {
                StreamAlert::where('id', $alertDataForDelete['id'])->delete();
            }
        }

        // vyhledání zda jsou data ve fornte na alerting
        if (ChannelsWhichWaitingForNotification::where('id', $request->streamId)->first()) {
            foreach (ChannelsWhichWaitingForNotification::where('id', $request->streamId)->get() as $waitingDataForDelete) {
                ChannelsWhichWaitingForNotification::where('id', $waitingDataForDelete['id'])->delete();
            }
        }

        return [
            'isAlert' => "isAlert",
            'status' => "success",
            'msg' => "Stream byl odebrán!"
        ];
    }
}
