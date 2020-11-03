<?php

namespace App\Http\Controllers;

use App\Console\Commands\SendSuccessEmail;
use App\Events\StreamNotification;
use App\Models\ChannelsWhichWaitingForNotification;
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

        $ffprobeOutput = shell_exec("timeout --foreground 10s ffprobe -v quiet -print_format json -show_entries stream=bit_rate -show_programs {$streamUrl} -timeout 10");

        $ffprobeJsonOutput = json_decode($ffprobeOutput, true);

        // získání informací o streamu
        $streamInfoData = Stream::where('id', $streamId)->first();

        if (!is_null($audioVideoCheck)) {

            //  proběhne kontrola, kdy se bude hlidat, resync audio / video
            self::check_if_stream_has_resync_audio_video($ffprobeJsonOutput, $streamId);
            return;
        } else {



            // pokud $ffprobeJsonOutput neobsahuje klíč "programs" => stream nefunguje ===> ukládá se error
            if (!array_key_exists("programs", $ffprobeJsonOutput)) {

                if ($streamInfoData->status != "error") {
                    Stream::where('id', $streamId)->update(['status' => "error"]);

                    StreamHistory::create([
                        'stream_id' => $streamId,
                        'status' => "stream_error"
                    ]);

                    // založení do tabulky channels_which_waiting_for_notifications, pokud jiz neexistuje
                    // overení, zda stream má povolenou notifikaci
                    if ($streamInfoData->sendMailAlert == true) {
                        if (!ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->first()) {
                            // vytvorení záznamu
                            ChannelsWhichWaitingForNotification::create([
                                'stream_id' => $streamId,
                                'whenToNotify' => date("Y-m-d H:i", strtotime('+5 minutes'))
                            ]);
                        }
                    }
                }
                // event(new StreamNotification());
            } else {
                // byl nalezen klíc programs , stream nejspise funguje

                // v teto fázy zatím jen ulozit stav success , zatím se nehledají žádné chyby a pod...
                if ($streamInfoData->status != 'success') {

                    Stream::where('id', $streamId)->update(['status' => "success"]);

                    StreamHistory::create([
                        'stream_id' => $streamId,
                        'status' => "stream_ok"
                    ]);

                    // kanál není ve statusu error, dojde k vyhledání, zda existuje v tabulce channels_which_waiting_for_notifications a odebrání
                    if (ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->where('notified', "!=", 'false')->first()) {
                        // odeslání mail notifikace pokud je zapotřebí
                        dispatch(new SendSuccessEmail($streamId));

                        // odebrání záznamu z tabulky
                        ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->delete();
                    }
                }
            }
        }
    }



    /**
     * funkce na kontroloru zda existuje resync audio / video
     *
     * @return void
     */
    public static function check_if_stream_has_resync_audio_video($ffprobeJson, $streamId): void
    {
        // vyhledání informací o streamu, aby se medelal pro kazdý blok insert => získání statusu streamu
        $streamInfo = Stream::where('id', $streamId)->first();

        // kotrola zda v poli existuje klic "programs" , nikdy by neměla být nesplněná tato podmínka
        if (!array_key_exists("programs", $ffprobeJson)) {
            if ($streamInfo->status != "error") {
                Stream::where('id', $streamId)->update(['status' => "error"]);

                StreamHistory::create([
                    'stream_id' => $streamId,
                    'status' => "stream_error"
                ]);

                // založení do tabulky channels_which_waiting_for_notifications, pokud jiz neexistuje
                // overení, zda stream má povolenou notifikaci
                if ($streamInfo->sendMailAlert == true) {
                    if (!ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->first()) {
                        // vytvorení záznamu
                        ChannelsWhichWaitingForNotification::create([
                            'stream_id' => $streamId,
                            'whenToNotify' => date("Y-m-d H:i", strtotime('+5 minutes'))
                        ]);
                    }
                }
            }
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
                    // // event(new StreamNotification());
                    // return;
                }

                if (array_key_exists("start_time", $program)) {
                    $orig_start_time = $program["start_time"];
                    $start_time = round($program["start_time"], 0);
                }
            }

            foreach ($ffprobeJson["programs"][0]["streams"] as $streams) {

                if (array_key_exists("codec_type", $streams)) {
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
                        // event(new StreamNotification());
                        // return;
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
                // event(new StreamNotification());
                // return;
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
                // event(new StreamNotification());
                // return;
            }
        }
    }
}
