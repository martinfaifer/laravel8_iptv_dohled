<?php

namespace App\Http\Controllers;

use App\Models\CcError;
use App\Models\Stream;
use App\Models\StreamAlert;
use App\Models\StreamHistory;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\StreamNotificationLimit;
use Illuminate\Http\Request;
use App\Jobs\SendSuccessEmail;
use App\Models\StopedStream;
use App\Models\SystemHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Validator;

class StreamController extends Controller
{

    use NotificationTrait;

    /**
     * funkce na vypsání všecj informací o streamu
     *
     */
    public function index()
    {
        return Stream::all();
    }

    /**
     * update statusu u streamu
     *
     * @param string $streamId
     * @param string $status issue || success
     * @return void
     */
    public static function queue_diagnostic_update_stream_status(object $stream, string $status): void
    {
        if ($status != "success") {
            $stream->update([
                'status' => $status
            ]);
            exit();
        }

        $stream->update([
            'status' => $status
        ]);

        if (StreamHistory::where([['stream_id', $stream->id], ['status', '!=', 'stream_ok']])->latest()->first()) {
            // ZÁZNAM DO HISTORIE, ŽE STREAM JE OK
            StreamHistory::create([
                'stream_id' => $stream->id,
                'status' => "stream_ok"
            ]);
        }

        StreamAlert::where('stream_id',  $stream->id)->each(function ($alertToDelete) {
            StreamAlert::where('id', $alertToDelete["id"])->delete();
        });

        if (ChannelsWhichWaitingForNotification::where('stream_id', $stream->id)->first()) {
            // odeslání mail notifikace pokud je zapotřebí
            dispatch(new SendSuccessEmail($stream->id));
        }
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
     * funkce, která vrátí kanály v mozaice
     *
     * @param Request $request
     * @return array
     */
    public function streams_for_mozaiku(): object
    {
        $user = Auth::user();
        return Stream::where('dohledovano', true)->where('status', '!=', 'stop')->orderBy('nazev', 'asc')->paginate($user->pagination, ['id', 'image', 'nazev', 'status']);
    }

    public function error_streams_for_mozaika(): array
    {
        if (!Stream::where([['dohledovano', true], ['status', 'error']])->first()) {
            return [
                'status' => "empty"
            ];
        }

        return [
            'status' => "success",
            'data' => Stream::where([['dohledovano', true], ['status', 'error']])->orderBy('nazev', 'asc')->get(['id', 'image', 'nazev', 'status'])
        ];
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
        $stream = Stream::find($request->streamId);
        if ($stream) {

            if (Cache::has("stream" . $stream->id)) {
                $streamInCache =  Cache::get("stream" . $stream->id);
                return [
                    'status' => $streamInCache['status']
                ];
            }

            return [
                'status' => $stream->status
            ];
        }
        return [
            'status' => 'not_found'
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


        if ($request->dohledovano === true) {
            // Zjistení puvodního statusu, pokud je jiný než stop, nemění se
            if ($stream_to_edit->status === "stop") {
                $status = "waiting";
            }
        } else {
            $status = "stop";

            // kill vsech procesů co běží na pozadí u streamu
            if (!is_null($stream_to_edit->process_pid)) {
                StreamDiagnosticController::stop_diagnostic_stream_from_backend($stream_to_edit->process_pid);
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
                'dohledVolume' => $request->dohledVolume ?? 0,
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
            'dohledVolume' => $request->dohledVolume ?? 0,
            'vytvaretNahled' => $request->vytvaretNahled ?? 0,
            'sendMailAlert' => $request->sendMailAlert ?? 0,
            'sendSmsAlert' => $request->sendSmsAlert ?? 0
        ]);

        return $this->frontend_notification("success", "Upraveo!");
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
    public static function delete_all_stream_information(string $streamId): void
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
            'dohledovano' => ['required'],
            'dohledVolume' => ['required'],
            'vytvaretNahled' => ['required'],
            'sendMailAlert' => ['required'],
            'sendSmsAlert' => ['required']
        ]);

        if ($validation->fails()) {
            return self::frontend_notification('error', 'Nebylo vše vyplněno!');
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
                'dohledVolume' => $request->audioDohled,
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
     * fn pro výpočet funkčních || negunkčních streamů
     *
     * @return void
     */
    public static function take_count_of_working_streams(): void
    {
        if (Stream::first()) {
            SystemHistory::create([
                'value' => Stream::where([['status', "!=", "waiting"], ['status', "!=", 'stop'], ['status', "!=", "error"]])->count(),
                'value_type' => "streams"
            ]);
        }
    }

    /**
     * fn pro vykreslení areachartu pro dashboard
     *
     * @return array
     */
    public function retun_count_of_working_streams(): array
    {
        if (SystemHistory::where('value_type', "streams")->first()) {

            foreach (SystemHistory::where('value_type', "streams")->get() as $streamsDataHistory) {
                $seriesData[] = $streamsDataHistory->value;
                $xaxis[] = substr($streamsDataHistory->created_at, 0, 19);
            }

            return [
                'status' => "exist",
                'xaxis' => $xaxis,
                'seriesData' => $seriesData
            ];
        } else {
            return [
                'status' => "empty"
            ];
        }
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
        $pocetFunkcnich = Stream::where('status', "success")->count();
        $pocetProblemovych = Stream::where('status', "issue")->count();
        $pocetStopnutych = Stream::where('status', "stop")->count();
        $pocetNefunkcnich = Stream::where('status', "error")->count();

        array_push($pocet, $pocetCekajicich, $pocetFunkcnich, $pocetProblemovych, $pocetStopnutych, $pocetNefunkcnich);
        // statusy
        // waiting, success, issue, stop

        // count jednotlivých statusů
        return [
            'status' => "success",
            'seriesDonut' => $pocet,
            'chartOptionsDonut' => array(
                'labels' => ['čekající', 'funkční', 'problémové', 'stopnuté', 'nefunkční']
            )
        ];
    }


    public function get_last_ten(): array
    {
        if (Stream::first()) {
            return Stream::orderByDesc('id')->take(10)->get('nazev')->toArray();
        } else {
            return [];
        }
    }

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
                    'img' => \Config::get('app.url') . "/" . $stream->image,
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
}
