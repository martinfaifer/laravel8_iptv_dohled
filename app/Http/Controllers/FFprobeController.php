<?php

namespace App\Http\Controllers;

use App\Events\StreamNotification;
use App\Models\Stream;
use App\Models\StreamAlert;
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
     * @param string $audioVideoCheck
     * @return void
     */
    public static function ffprobe_diagnostic($streamUrl, $streamId, $audioVideoCheck = null): void
    {

        $ffprobeOutput = shell_exec("ffprobe -v quiet -print_format json -show_entries stream=bit_rate -show_programs {$streamUrl} -timeout 4");

        $ffprobeJsonOutput = json_decode($ffprobeOutput, true);

        if (!is_null($audioVideoCheck)) {

            //  proběhne kontrola, kdy se bude hlidat, resync audio / video
            self::check_if_stream_has_resync_audio_video($ffprobeOutput, $streamId);
            return;
        } else {



            // pokud $ffprobeJsonOutput neobsahuje klíč "programs" => stream nefunguje ===> ukládá se error
            if (!array_key_exists("programs", $ffprobeJsonOutput)) {

                Stream::where('id', $streamId)->update(['status' => "error"]);


                if (!StreamHistory::where('stream_id', $streamId)->latest()->first()->status == "stream_error") {
                    // poslední status u kanálu není stream_error ( nefunkční ), založí se nový status
                    StreamHistory::create([
                        'stream_id' => $streamId,
                        'status' => "stream_error"
                    ]);
                    event(new StreamNotification());
                }
            } else {
                // byl nalezen klíc programs , stream nejspise funguje

                // v teto fázy zatím jen ulozit stav success , zatím se nehledají žádné chyby a pod...
                Stream::where('id', $streamId)->update(['status' => "success"]);
                event(new StreamNotification());
                // if (!StreamHistory::where('stream_id', $streamId)->orderBy('created_at', 'asc')->first()->status == "stream_ok") {
                //     // poslední status u kanálu není stream_stream_okerror ( funkční ), založí se nový status
                //     StreamHistory::create([
                //         'stream_id' => $streamId,
                //         'status' => "stream_ok"
                //     ]);
                //     event(new StreamNotification());
                //     return;
                // }
            }
        }
    }



    /**
     * funkce na kontroloru zda existuje resync audio / video
     *
     * @return void
     */
    public static function check_if_stream_has_resync_audio_video($ffprobeOutput, $streamId): void
    {
        // vyhledání informací o streamu, aby se medelal pro kazdý blok insert => získání statusu streamu
        $streamInfo = Stream::where('id', $streamId)->first();

        // převedení do json
        $ffprobeJson = json_decode($ffprobeOutput, true);

        // kotrola zda v poli existuje klic "programs" , nikdy by neměla být nesplněná tato podmínka
        if (!array_key_exists("programs", $ffprobeJson)) {
            // tato situace nikdy nenastane, jelikož podminka v parent fn toto resi
            return;
        } else {

            // vyhledání pcr_pidu
            foreach ($ffprobeJson["programs"] as $program) {
                if (array_key_exists("pcr_pid", $program)) {
                    $pcrPid = $program["pcr_pid"];
                } else {

                    // pokud pcr_pid neexsituje, stav streamu se mění na issue, nejspíše je resync audio / video , tudíž záznam do stream_alerts a stream_histories
                    if ($streamInfo->status == "success") {
                        Stream::where('id', $streamId)->update(['status' => "issue"]);
                        if (!StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->first()) {
                            // vytvorení záznamu do stream_alerts
                            StreamAlert::create([
                                'stream_id' => $streamId,
                                'status' => "invalidSync_warning",
                                'message' => "Desynchronizace Audia / videa"
                            ]);

                            // ulozeni do historie
                            StreamHistory::create([
                                'stream_id' => $streamId,
                                'status' => "invalidSync_warning"
                            ]);
                        }
                    }
                    event(new StreamNotification());
                    return;
                }

                if (array_key_exists("start_time", $program)) {
                    $orig_start_time = $program["start_time"];
                    $start_time = round($program["start_time"], 0);
                }
            }

            foreach ($ffprobeJson["programs"][0]["streams"] as $streams) {

                if ($streams["codec_type"] == "video") {

                    if (array_key_exists("id", $streams)) {
                        $streamId = hexdec($streams["id"]);
                    } else {
                        // pokud pcr_pid neexsituje, stav streamu se mění na issue, nejspíše je resync audio / video , tudíž záznam do stream_alerts a stream_histories
                        if ($streamInfo->status == "success") {
                            Stream::where('id', $streamId)->update(['status' => "issue"]);
                            if (!StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->first()) {
                                // vytvorení záznamu do stream_alerts
                                StreamAlert::create([
                                    'stream_id' => $streamId,
                                    'status' => "invalidSync_warning",
                                    'message' => "Desynchronizace Audia / videa"
                                ]);

                                // ulozeni do historie
                                StreamHistory::create([
                                    'stream_id' => $streamId,
                                    'status' => "invalidSync_warning"
                                ]);
                            }
                        }
                        event(new StreamNotification());
                        return;
                    }

                    if (array_key_exists("start_time", $streams)) {
                        $orig_video_start_time = $streams["start_time"];
                        $video_start_time = round($streams["start_time"], 0);
                    }
                }

                if ($streams["codec_type"] == "audio") {

                    if (array_key_exists("start_time", $streams)) {
                        $orig_audio_start_time = $streams["start_time"];
                        $audio_start_time = round($streams["start_time"], 0);
                    }
                }
            }



            if ($streamId == $pcrPid) {

                if (isset($start_time) && isset($video_start_time) && isset($audio_start_time)) {

                    if ($start_time == $video_start_time && $start_time == $audio_start_time) {

                        // update, pokud stav není success

                        // vse je v pořádku, hodnoty jsou totožné, kontrola , zda stream má hodnotu success, pripadně smazání alertu
                        if ($streamInfo->status != "success") {
                            Stream::where('id', $streamId)->update(['status' => "success"]);
                            if (StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->first()) {
                                // odebrání záznamu ze stream_alerts
                                StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->delete();

                                // ulozeni do historie, že kanál je ynnyí OK
                                StreamHistory::create([
                                    'stream_id' => $streamId,
                                    'status' => "audio_OK"
                                ]);
                            }
                        }
                        event(new StreamNotification());
                        return;
                    } else {

                        $checkPrimarToVideo = intval($video_start_time) - intval($start_time);
                        $checkPrimarToAudio = intval($audio_start_time) - intval($start_time);

                        if ($checkPrimarToVideo <= 1 &&  $checkPrimarToAudio <= 1) {
                            // update, pokud stav není success

                            // vse je v pořádku, hodnoty jsou totožné, kontrola , zda stream má hodnotu success, pripadně smazání alertu
                            if ($streamInfo->status != "success") {
                                Stream::where('id', $streamId)->update(['status' => "success"]);
                                if (StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->first()) {
                                    // odebrání záznamu ze stream_alerts
                                    StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->delete();

                                    // ulozeni do historie, že kanál je ynnyí OK
                                    StreamHistory::create([
                                        'stream_id' => $streamId,
                                        'status' => "audio_OK"
                                    ]);
                                }
                            }
                            event(new StreamNotification());
                            return;
                        } else {

                            $orig_start_time = explode(".", $orig_start_time);
                            $orig_video_start_time = explode(".", $orig_video_start_time);
                            $orig_audio_start_time = explode(".", $orig_audio_start_time);

                            if ($orig_start_time[0] == $orig_video_start_time[0] && $orig_start_time[0] == $orig_audio_start_time[0]) {
                                // update, pokud stav není success

                                // vse je v pořádku, hodnoty jsou totožné, kontrola , zda stream má hodnotu success, pripadně smazání alertu
                                if ($streamInfo->status != "success") {
                                    Stream::where('id', $streamId)->update(['status' => "success"]);
                                    if (StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->first()) {
                                        // odebrání záznamu ze stream_alerts
                                        StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->delete();

                                        // ulozeni do historie, že kanál je ynnyí OK
                                        StreamHistory::create([
                                            'stream_id' => $streamId,
                                            'status' => "audio_OK"
                                        ]);
                                    }
                                }
                                event(new StreamNotification());
                                return;
                            }
                        }

                        // podmínka nebyla splněná

                        if ($streamInfo->status == "success") {
                            Stream::where('id', $streamId)->update(['status' => "issue"]);
                            if (!StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->first()) {
                                // vytvorení záznamu do stream_alerts
                                StreamAlert::create([
                                    'stream_id' => $streamId,
                                    'status' => "invalidSync_warning",
                                    'message' => "Desynchronizace Audia / videa"
                                ]);

                                // ulozeni do historie
                                StreamHistory::create([
                                    'stream_id' => $streamId,
                                    'status' => "invalidSync_warning"
                                ]);
                            }
                        }
                        event(new StreamNotification());
                        return;
                    }
                }
                // update, pokud stav není success

                // vse je v pořádku, hodnoty jsou totožné, kontrola , zda stream má hodnotu success, pripadně smazání alertu
                if ($streamInfo->status != "success") {
                    Stream::where('id', $streamId)->update(['status' => "success"]);
                    if (StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->first()) {
                        // odebrání záznamu ze stream_alerts
                        StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->delete();

                        // ulozeni do historie, že kanál je ynnyí OK
                        StreamHistory::create([
                            'stream_id' => $streamId,
                            'status' => "audio_OK"
                        ]);
                    }
                }
                event(new StreamNotification());
                return;
            } else {

                if ($streamInfo->status == "success") {
                    Stream::where('id', $streamId)->update(['status' => "issue"]);
                    if (!StreamAlert::where('stream_id', $streamId)->where('status', "invalidSync_warning")->first()) {
                        // vytvorení záznamu do stream_alerts
                        StreamAlert::create([
                            'stream_id' => $streamId,
                            'status' => "invalidSync_warning",
                            'message' => "Desynchronizace Audia / videa"
                        ]);

                        // ulozeni do historie
                        StreamHistory::create([
                            'stream_id' => $streamId,
                            'status' => "invalidSync_warning"
                        ]);
                    }
                }
                event(new StreamNotification());
                return;
            }
        }
    }
}
