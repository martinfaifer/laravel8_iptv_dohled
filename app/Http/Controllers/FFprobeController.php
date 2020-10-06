<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamHistory;
use Illuminate\Http\Request;

class FFprobeController extends Controller
{

    /**
     * funkce pro spustení ffproby u streamů, které TSDuck nedokázal diagnostikovat
     *
     * funkce funguje v queue
     *
     * @param string $streamUrl
     * @param string $streamId
     * @return void
     */
    public static function ffprobe_diagnostic(string $streamUrl, string $streamId): void
    {

        $ffprobeOutput = shell_exec("ffprobe -v quiet -print_format json -show_entries stream=bit_rate -show_programs {$streamUrl} -timeout 10");

        $ffprobeJsonOutput = json_decode($ffprobeOutput, true);

        // pokud $ffprobeJsonOutput neobsahuje klíč "programs" => stream nefunguje ===> ukládá se error
        if (!array_key_exists("programs", $ffprobeJsonOutput)) {

            Stream::where('id', $streamId)->update(['status' => "error"]);


            if (!StreamHistory::where('stream_id', $streamId)->latest()->first()->status == "stream_error") {
                // poslední status u kanálu není stream_error ( nefunkční ), založí se nový status
                StreamHistory::create([
                    'stream_id' => $streamId,
                    'status' => "stream_error"
                ]);
            }
        } else {
            // byl nalezen klíc programs , stream nejspise funguje

            // v teto fázy zatím jen ulozit stav success , zatím se nehledají žádné chyby a pod...
            Stream::where('id', $streamId)->update(['status' => "success"]);

            if (!StreamHistory::where('stream_id', $streamId)->latest()->first()->status == "stream_ok") {
                // poslední status u kanálu není stream_stream_okerror ( funkční ), založí se nový status
                StreamHistory::create([
                    'stream_id' => $streamId,
                    'status' => "stream_ok"
                ]);
            }
        }
    }
}
