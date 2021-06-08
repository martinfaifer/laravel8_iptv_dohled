<?php

namespace App\Http\Controllers;

use App\Jobs\FFProbeJob;
use App\Models\Stream;
use App\Models\StreamAlert;
use Illuminate\Support\Facades\Cache;
use App\Models\StopedStream;
use App\Models\StreamHistory;
use Illuminate\Http\Request;

use App\Traits\NotificationTrait;

class StreamDiagnosticController extends Controller
{

    use NotificationTrait;
    /**
     * funkce pro spustení vsech streamu, které se nediagnostikují ( chyby jim process_pid ==> null )
     *
     * @return void
     */
    public static function start_streams_for_diagnostic(): void
    {
        Stream::where([['dohledovano', true], ['status', 'waiting']])->chunk(50, function ($streamsToStart) {
            foreach ($streamsToStart as $streamToStart) {
                // overení zda neexistuje zaznam v cache
                if (!Cache::has("stream" . $streamToStart->id)) {
                    $processPid = shell_exec("nohup php artisan command:start_realtime_diagnostic_and_return_pid {$streamToStart->stream_url} {$streamToStart->id}" . ' > /dev/null 2>&1 & echo $!; ');
                    self::check_and_store_pid("process_pid", $processPid, $streamToStart);
                }
            }
        });
    }

    public static function check_stream_audio_video(): void
    {
        Stream::where('status', 'running')->chunk(50, function ($streamsToCheck) {
            foreach ($streamsToCheck as $streamToCheck) {
                // job
                FFProbeJob::dispatchNow($streamToCheck->id, $streamToCheck->stream_url);
            }
        });
    }


    /**
     * fn pro pokus o spustení streamu, který je ve stavu error
     *
     * @return void
     */
    public static function try_start_error_stream(): void
    {
        if (Stream::where('status', 'error')->first()) {
            // existuje minimálně jeden stream. který má process_pid == null a zároveň nemá status stop
            Stream::where('status', 'error')->each(function ($streamToStart) {
                if (!StopedStream::where('streamId', "!=", $streamToStart->id)->first()) {

                    // killnutí puvodních pidů
                    if (!is_null($streamToStart->process_pid)) {
                        self::stop_diagnostic_stream_from_backend($streamToStart->process_pid);
                    }

                    // pokud existuje, je uvaha, ze se od tohoto upusti
                    if (!is_null($streamToStart->socket_process_pid)) {
                        self::stop_diagnostic_stream_from_backend($streamToStart->socket_process_pid);
                    }

                    // pro každý stream spustí diagnostiku
                    // spustení příkazu a vrácení pidu
                    $processPid = shell_exec("nohup php artisan command:start_realtime_diagnostic_and_return_pid {$streamToStart->stream_url} {$streamToStart->id}" . ' > /dev/null 2>&1 & echo $!; ');

                    self::check_and_store_pid("process_pid", $processPid, $streamToStart->id);
                }
            });
        }
    }

    /**
     * funkce, která ověřuje, že všechny streamy, které mají u sebe uložené pidy funguje korektně
     *
     * pokud pid není nalezen ==> ukončení veškerých dalších processů, které souvysí se streamem
     *
     * @return void
     */
    public static function check_if_streams_running_corectly(): void
    {
        // Vyhledání zda funguje nějaký stream
        if (Stream::where('dohledovano', true)->where('status', "success")->orWhere('status', "issue")->first()) {   // => zde se berou vsechny streamy, které by se měli dohledovat respektive, které se dohledují

            Stream::where('dohledovano', true)->where('status', "success")->orWhere('status', "issue")->get()->each(function ($streamsToCheck) {
                // kontrola, zda stream funguje
                // pokud nefunguje, vrací not_running
                if (SystemController::check_if_process_running(intval($streamsToCheck->process_pid)) == "not_running") {

                    // ukocení streamu, dle pidu pod var process_pid
                    self::stop_diagnostic_stream_from_backend(intval($streamsToCheck->process_pid));

                    // kontrola, zda stream neexistuje v tabulce stoped_streams ( kontrola probíhá dle id streamu )
                    if (!StopedStream::where('streamId', $streamsToCheck->id)->first()) {

                        // zapsaání záznamu do historie
                        StreamHistory::create([
                            'stream_id' => $streamsToCheck->id,
                            'status' => "streamCrash_tryToStart"
                        ]);


                        $processPid = shell_exec("nohup php artisan command:start_realtime_diagnostic_and_return_pid {$streamsToCheck->stream_url} {$streamsToCheck->id}" . ' > /dev/null 2>&1 & echo $!; ');
                        self::check_and_store_pid("process_pid", intval($processPid), $streamsToCheck->id);
                    }
                }
            });
        }
    }


    /**
     * funkce pro ukončení všech streamů co aktuálně fungují
     *
     * u každého streamu proběhne update process_pid => null ffmpeg_pid => null status => waiting , socket_process_pid => null
     *
     * @return array
     */
    public static function kill_all_running_streams(): array
    {
        // vyhledání zda existuje jakýkoliv stream pro ukoncení
        if (Stream::first()) {

            foreach (Stream::all() as $streamProUkonceni) {
                //  ukončení ffmpegu, pokud aktuálně funguje
                if (!is_null($streamProUkonceni->ffmpeg_pid)) {
                    self::stop_diagnostic_stream_from_backend($streamProUkonceni->ffmpeg_pid);
                }
                if (!is_null($streamProUkonceni->process_pid)) {
                    self::stop_diagnostic_stream_from_backend($streamProUkonceni->process_pid);
                }

                $streamProUkonceni->update(
                    [
                        'status' => "waiting",
                        'process_pid' => null,
                        'ffmpeg_pid' => null,
                        'socket_process_pid' => null
                    ]
                );
            }

            return self::frontend_notification("success", "success", "Streamy byli ukončeny!");
        } else {
            return self::frontend_notification("info", "info", "Nebyl nalezen žádný spuštěný stream!");
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
     * funkce na overení a ulození pidu
     *
     * @param string $postion
     * @param string $processPid
     * @param object $stream
     * @return void
     */
    public static function check_and_store_pid(string $postion, string $processPid, object $stream): void
    {
        // ne vždy bude existovat ffmpegPid, takže s ním se nebude počítat protože se dohleduje v samostatném procesu
        if (SystemController::check_if_process_running(intval($processPid)) !== "running") {

            // sluzby nefungují jak by meli -> streamDiagnotika a tvorba náhledů
            // do streamu ulozime chybu a do streamAlertu vytvorime zaznam se statusem start_error

            $stream->update(['status' => "start_error"]);

            if (!Cache::has("stream" . $stream->id)) {
                Cache::put("stream" . $stream->id, [
                    'status' => "error",
                    'stream' => $stream->nazev,
                    'msg' => "Nepodařilo se spustit stream"
                ]);
                StreamHistoryController::create($stream->id, "start_error");
                $stream->update([
                    'status' => "start_error"
                ]);
            }

            // vyhledání, který pid nefunguje a killnutí pidu, který aktuálně funguje navíc, aby zbytečně nebral resourcess na serveru
            if (SystemController::check_if_process_running(intval($processPid)) == "running") {
                self::stop_diagnostic_stream_from_backend(intval($processPid));
            }
        }

        // uložení pidu do tabulky k streamu
        $stream->update([
            $postion => intval($processPid),
            'status' => "running"
        ]);
        // vyhledání zda existuje záznam v tabulce stream_alerts se statusem start_error a stream_id streamu, co nyní spoustíme
        if (StreamAlert::where([['stream_id', $stream->id], ['status', "start_error"]])->first()) {
            // existuje záznam, dojde k jeho odebrání
            StreamAlert::where([['stream_id', $stream->id], ['status', "start_error"]])->delete();
        }
    }
}
