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

use App\Jobs\FFProbeJob;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\StopedStream;
use Illuminate\Support\Facades\Log;
use React\EventLoop\Factory;
use Illuminate\Support\Facades\Cache;

use App\Traits\StringToReadeableTrait;
use App\Traits\TSDuckTrait;

class DiagnosticController extends Controller
{
    use StringToReadeableTrait;
    use TSDuckTrait;

    public $ts_data = null;
    public $pids = null;
    public $pids_data = null;

    /**
     * hlavní funkce. která v realném čase dohleduje jednotlivé streamy
     * u kanálu je uložen process_pid pod kterým funguje tento loop
     *
     * pro ukonceni je znám pid processu, který se následně killne
     *
     */
    public static function stream_realtime_diagnostic_and_return_status($stream): void
    {
        try {
            Log::info("stream {$stream->id} je spusten");

            /**
             * ---------------------------------------------------------------------------------------------------------------------------------------
             * EVENT LOOP
             * vše co je v eventLoop , funguje async => rychlejší odbavení / zpracování dat bez nutnosti cekání, I/O no blocking
             * ---------------------------------------------------------------------------------------------------------------------------------------
             */
            $eventLoop = Factory::create();

            $eventLoop->addPeriodicTimer(1, function () use ($stream) {
                // overení zda stream nemá status stop
                // získání informací o streamu

                if (!StopedStream::where('streamId', "!=", $stream->id)->first()) {

                    // spuštění tsducku pro diagnostiku kanálu ip = multicast http = hls
                    $tsDuckData = self::analyze($stream->stream_url);

                    match ($tsDuckData) {
                        'Killed' => $this->analyze_is_killed($stream),
                        empty($tsDuckData) => $this->analyze_is_empty($stream),
                        default =>  $this->analyze_is_filled($tsDuckData, $stream)
                    };
                }
            });

            $eventLoop->run();
        } catch (\Throwable $th) {
            Log::error("{$stream->id} => $th");
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
    protected function analyze_is_filled(string $tsDuckData, object $stream): void
    {
        $tsduckArr = $this->convert_tsduck_string_to_array($tsDuckData);
        if (is_array($tsduckArr)) {
            $this->analyze_array_from_tsduck($tsduckArr,  $stream);
        }
    }

    /**
     * oznacení streamu v historii, ze byl killnut
     *
     * @param object $stream
     * @return void
     */
    protected function analyze_is_killed(object $stream): void
    {
        if ($stream->status != "error") {

            $stream->update(['status' => "error"]);

            StreamHistoryController::create($stream->id, "stream_without_signal");
        }
    }

    /**
     * alanýla proběhla, ale je prázdná, z toho důvodu se předpokládá, že stream není funkční
     *
     * @param object $stream
     * @return void
     */
    protected function analyze_is_empty(object $stream): void
    {
        if ($stream->status != "error") {
            $stream->update(['status' => "error"]);

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

    protected function analyze_array_from_tsduck(array $tsduck, object $stream): void
    {
        try {
            $eventLoop = Factory::create();
            // kanál není ve statusu error, dojde k vyhledání, zda existuje v tabulce channels_which_waiting_for_notifications a odebrání
            ChannelsWhichWaitingForNotification::where('stream_id', $stream->id)->first()->delete();

            // zpracování pole
            // vyhledání specifických klíčů, dle kterých se pole zpracuje
            if (array_key_exists('ts', $tsduck)) {
                // pokud je vse v poradku vraci pole "status" => "success"
                // pokud bude jakakoliv chyba ,  raci pole "status" => "issue" , "dataAlert" => ["status" => "status", "message" => "message"]
                $this->ts_data = Analyze_TransportStreamController::collect_transportStream_from_tsduckArr($tsduck["ts"], $stream->id);
            }

            // zobrazení servisních informací
            if (array_key_exists('service', $tsduck)) {
                Analyze_ServicesOfStreamController::collect_service_from_tsduckArr($tsduck["service"], $stream->id);
            }

            // array || null video, array || null audio, array || null ca
            // tato funkce je nejdulezitejsi z cele diagnostiky
            if (array_key_exists('pids', $tsduck)) {
                $this->pids = Analyze_PidStreamController::collect_pids_from_tsduckArr($tsduck["pids"]);
                // Zpracování pidů
                // od teto analýzy se odvíjí téměř veškeré informace o streamu
                // pokud je vse v poradku vraci pole "status" => "success"
                // pokud bude jakakoliv chyba ,  raci pole "status" => "issue" , "alert_status" => "status", "msg" => "Nejaka message"
                $this->pids_data = Analyze_PidStreamController::analyze_pids_and_storeData($this->pids, $stream->id);
            }

            // analyzování výstupu z jednotlivých podruzných funkcí
            if (is_array($this->ts_data)) {
                if (is_array($this->pids_data)) {

                    if ($this->ts_data["status"] === "success" && $this->pids_data["status"] === "success") {

                        if (Cache::has("stream" . $stream->id)) {
                            // odebrání z cache
                            Cache::pull("stream" . $stream->id);
                            // zapsání do historie -> stream_ok
                            StreamHistoryController::create($stream->id, "stream_ok");
                        }
                    }

                    if ($this->ts_data["status"] != "success") {

                        if (array_key_exists("alert_status", $this->ts_data)) {
                            if (!Cache::has("stream" . $stream->id)) {

                                Cache::put("stream" . $stream->id, [
                                    'status' => "issue",
                                    'stream' => $stream->nazev,
                                    'msg' => $this->ts_data["msg"]
                                ]);
                                StreamHistoryController::create($stream->id, $this->ts_data["alert_status"]);
                            }
                        }
                    }

                    if ($this->pids_data["status"] != "success") {

                        if (array_key_exists("alert_status", $this->pids_data)) {
                            if (!Cache::has("stream" . $stream->id)) {
                                Cache::put("stream" . $stream->id, [
                                    'status' => "issue",
                                    'stream' => $stream->nazev,
                                    'msg' => $this->pids_data["msg"]
                                ]);
                                StreamHistoryController::create($stream->id, $this->pids_data["alert_status"]);
                            }
                        }
                    }
                }
            }
            $eventLoop->run();
        } catch (\Throwable $th) {
            // log / notifikace adminovi

        }
    }
}
