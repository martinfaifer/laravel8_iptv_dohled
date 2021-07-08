<?php

namespace App\Http\Controllers\Diagnostic;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Streams\StreamHistoryController;
use App\Http\Controllers\System\SystemController;
use App\Jobs\FFProbeJob;
use App\Models\Stream;
use App\Models\StreamAlert;
use Illuminate\Support\Facades\Cache;
use App\Models\StreamHistory;

use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheTrait;

class StreamDiagnosticController extends Controller
{
    use NotificationTrait, CacheTrait;

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
        Stream::where([['status', 'running'], ['is_problem', false]])->chunk(50, function ($streamsToCheck) {
            foreach ($streamsToCheck as $streamToCheck) {
                FFProbeJob::dispatch($streamToCheck->id, $streamToCheck->stream_url)
                    ->onConnection('redis')
                    ->onQueue('ffprobe');
            }
        });
    }


    public static function check_if_streams_running(): void
    {
        Stream::where([['dohledovano', true], ['status', 'running'], ['is_problem', false], ['dohledAudia', true]])->chunk(50, function ($streamsToCheck) {
            foreach ($streamsToCheck as $streamToCheck) {
                if (SystemController::check_if_process_running($streamToCheck->process_pid) != "running") {
                    Log::info("pokus znovu spustit stream " . $streamToCheck->nazev);
                    StreamHistoryController::create($streamToCheck->id, "stream_without_signal");
                    $processPid = shell_exec("nohup php artisan command:start_realtime_diagnostic_and_return_pid {$streamToCheck->stream_url} {$streamToCheck->id}" . ' > /dev/null 2>&1 & echo $!; ');
                    self::check_and_store_pid("process_pid", $processPid, $streamToCheck);
                }
            }
        });
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

            Stream::chunk(50, function ($streamsProUkonceni) {
                foreach ($streamsProUkonceni as $streamProUkonceni) {
                    if (!is_null($streamProUkonceni->process_pid)) {
                        self::stop_diagnostic_stream_from_backend($streamProUkonceni->process_pid);
                    }

                    $streamProUkonceni->update([
                        'status' => "waiting",
                        'process_pid' => null,
                        'ffmpeg_pid' => null,
                        'socket_process_pid' => null,
                        'is_problem' => false
                    ]);

                    StreamHistory::create([
                        'stream_id' => $streamProUkonceni->id,
                        'status' => "stream_stoped_by_user"
                    ]);
                }
            });

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
    public static function stop_diagnostic_stream_from_backend($pid, $stream = null): void
    {
        shell_exec("kill -9 {$pid}");
        if (!is_null($stream)) {
            $stream->update([
                'status' => "stop",
                'process_pid' => null
            ]);
        }
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
            self::store_stream_is_not_start($stream);

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
