<?php

/**
 *
 * ----------------------------------------------------------------------------------------------------------------------------------------------
 * DIAGNOSTICKÉ JÁDRO, KTERÉ ZODPOVÍDÁ ZA KONTROLU KANÁLŮ , POUŽÍVÁ SE PRIMÁRNĚ TSDUCK PRO DIAGNOSTIKU A FFPROBE PRO ZÍSKÁNÍ LOW LEVEL INFORMACÍ
 * --------------------------------------------------------------------------------------------------------------------------------------------
 *
 * JÁDRO VERZE 1.4.1
 *
 *
 */

namespace App\Http\Controllers;

use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\Stream;
use App\Models\StreamHistory;
use Illuminate\Support\Facades\Log;
use React\EventLoop\Factory;
use Illuminate\Support\Facades\Cache;

use App\Traits\StringToReadeableTrait;
use App\Traits\TSDuckTrait;
use App\Traits\SystemLogTrait;

class DiagnosticController extends Controller
{
    use StringToReadeableTrait;
    use TSDuckTrait;
    use SystemLogTrait;

    /**
     * hlavní funkce. která v realném čase dohleduje jednotlivé streamy
     * u kanálu je uložen process_pid pod kterým funguje tento loop
     *
     * pro ukonceni je znám pid processu, který se následně killne
     *
     */
    public static function stream_realtime_diagnostic_and_return_status(string $streamUrl, string $streamId): void
    {
        try {
            Log::info("stream {$streamId} je spusten");

            StreamHistory::create([
                'stream_id' => $streamId,
                'status' => "stream_start"
            ]);
            /**
             * ---------------------------------------------------------------------------------------------------------------------------------------
             * EVENT LOOP
             * vše co je v eventLoop , funguje async => rychlejší odbavení / zpracování dat bez nutnosti cekání, I/O no blocking
             * ---------------------------------------------------------------------------------------------------------------------------------------
             */
            $eventLoop = Factory::create();

            $eventLoop->addPeriodicTimer(1, function () use ($streamId, $streamUrl) {
                // overení zda stream nemá status stop
                // získání informací o streamu
                $stream = Stream::find($streamId);

                if ($stream->dohledovano == true) {
                    // spuštění tsducku pro diagnostiku kanálu ip = multicast http = hls
                    $tsDuckData = self::analyze($streamUrl);
                    if (str_contains($tsDuckData, 'pid:')) {
                        self::analyze_is_filled($tsDuckData, $stream);
                    } else {
                        self::analyze_is_killed($stream);
                    }
                } else {
                    StreamDiagnosticController::stop_diagnostic_stream_from_backend($stream->process_pid, $stream);
                }
            });

            $eventLoop->run();
        } catch (\Throwable $th) {
            Log::error("{$streamId} => $th");
            // odeslání mailu
            // uložení chyby
            self::create('diagnostika', $th);
        }
    }


    /**
     * analýza proběhla, je i plná dat,
     * převedení stringu z analýzi do pole,
     * předání dále do analýzi
     *
     * @param string $tsDuckData
     * @param object $stream
     * @return void
     */
    protected static function analyze_is_filled(string $tsDuckData, object $stream): void
    {
        $tsduckArr = self::convert_tsduck_string_to_array($tsDuckData);
        if (is_array($tsduckArr)) {
            if ($stream->status != 'running') {
                $stream->update([
                    'status' => "running"
                ]);
            }

            self::analyze_array_from_tsduck($tsduckArr,  $stream);
        }
    }

    /**
     * oznacení streamu v historii, ze byl killnut
     *
     * @param object $stream
     * @return void
     */
    protected static function analyze_is_killed(object $stream): void
    {
        if ($stream->status != 'running') {
            $stream->update([
                'status' => "running"
            ]);
        }

        if (!Cache::has("stream" . $stream->id)) {

            Cache::put("stream" . $stream->id, [
                'status' => "error",
                'stream' => $stream->nazev,
                'msg' => "stream_ko"
            ]);

            StreamHistoryController::create($stream->id, "stream_without_signal");
            $stream->update(['is_problem' => true]);
        }
    }

    /**
     * alanýla proběhla, ale je prázdná, z toho důvodu se předpokládá, že stream není funkční
     *
     * @param object $stream
     * @return void
     */
    protected static function analyze_is_empty(object $stream): void
    {
        if ($stream->is_problem != true) {
            $stream->update(['is_problem' => true]);

            StreamHistoryController::create($stream->id, "stream_error");

            // založení do tabulky channels_which_waiting_for_notifications, pokud jiz neexistuje
            // overení, zda stream má povolenou notifikaci
            if ($stream->sendMailAlert === true) {
                ChannelsWhichWaitingForNotification::where('stream_id', $stream->id)->firstOrCreate([
                    'stream_id' => $stream->id,
                    'whenToNotify' => date("Y-m-d H:i", strtotime('+5 minutes'))
                ]);
            }
        }
    }

    protected static function analyze_array_from_tsduck(array $tsduck, object $stream): void
    {
        $ts_data = null;
        $pids = null;
        $pids_data = null;

        // kanál není ve statusu error, dojde k vyhledání, zda existuje v tabulce channels_which_waiting_for_notifications a odebrání
        if ($notificationToDelete = ChannelsWhichWaitingForNotification::where('stream_id', $stream->id)->first()) {
            $notificationToDelete->delete();
        }

        try {
            $eventLoop = Factory::create();

            // zpracování pole
            // vyhledání specifických klíčů, dle kterých se pole zpracuje
            if (array_key_exists('ts', $tsduck)) {
                // pokud je vse v poradku vraci pole "status" => "success"
                // pokud bude jakakoliv chyba ,  raci pole "status" => "issue" , "dataAlert" => ["status" => "status", "message" => "message"]
                $ts_data = Analyze_TransportStreamController::collect_transportStream_from_tsduckArr($tsduck["ts"], $stream->id);
            }

            // zobrazení servisních informací
            if (array_key_exists('service', $tsduck)) {
                Analyze_ServicesOfStreamController::collect_service_from_tsduckArr($tsduck["service"], $stream->id);
            }

            // array || null video, array || null audio, array || null ca
            // tato funkce je nejdulezitejsi z cele diagnostiky
            if (array_key_exists('pids', $tsduck)) {
                $pids = Analyze_PidStreamController::collect_pids_from_tsduckArr($tsduck["pids"]);
                // Zpracování pidů
                // od teto analýzy se odvíjí téměř veškeré informace o streamu
                // pokud je vse v poradku vraci pole "status" => "success"
                // pokud bude jakakoliv chyba ,  raci pole "status" => "issue" , "alert_status" => "status", "msg" => "Nejaka message"
                $pids_data = Analyze_PidStreamController::analyze_pids_and_storeData($pids, $stream->id);
            }

            // analyzování výstupu z jednotlivých podruzných funkcí
            if (is_array($ts_data)) {
                if (is_array($pids_data)) {

                    if ($ts_data["status"] === "success" && $pids_data["status"] === "success") {
                        if (Cache::has("stream" . $stream->id)) {
                            // odebrání z cache
                            Cache::pull("stream" . $stream->id);
                            // zapsání do historie -> stream_ok
                            StreamHistoryController::create($stream->id, "stream_ok");

                            $stream->update([
                                'is_problem' => false
                            ]);
                        }
                    }

                    if ($ts_data["status"] != "success") {

                        if (array_key_exists("alert_status", $ts_data)) {
                            if (!Cache::has("stream" . $stream->id)) {

                                Cache::put("stream" . $stream->id, [
                                    'status' => "issue",
                                    'stream' => $stream->nazev,
                                    'msg' => $ts_data["msg"]
                                ]);
                                StreamHistoryController::create($stream->id, $ts_data["alert_status"]);

                                $stream->update([
                                    'is_problem' => true
                                ]);
                            }
                        }
                    }

                    if ($pids_data["status"] != "success") {

                        if (array_key_exists("alert_status", $pids_data)) {
                            if (!Cache::has("stream" . $stream->id)) {
                                Cache::put("stream" . $stream->id, [
                                    'status' => "issue",
                                    'stream' => $stream->nazev,
                                    'msg' => $pids_data["msg"]
                                ]);
                                StreamHistoryController::create($stream->id, $pids_data["alert_status"]);

                                $stream->update([
                                    'is_problem' => true
                                ]);
                            }
                        }
                    }
                }
            }
            $eventLoop->run();
        } catch (\Throwable $th) {
            // log / notifikace adminovi
            self::create('analyze', $th);
        }
    }
}
