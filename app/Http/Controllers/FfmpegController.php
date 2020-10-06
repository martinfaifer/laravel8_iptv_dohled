<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;

class FfmpegController extends Controller
{


    /**
     * funknce pro spustení nekonecné smycky pro vtváření náhledu
     *
     * obrázky se uloží do složky storage/channelsImages/
     *
     * pokud již obrázek existuje, dojde k jeho smazání a následně se vytvoří nový
     *
     * vrátí $pid
     *
     * @param string $streamId
     * @param string $streamUrl
     * @return int $pid
     */
    public static function find_if_exist_image_delete_and_create_new_image_loop(string $streamId, string $streamUrl)
    {
        $pid = shell_exec("nohup ffmpeg -i {$streamUrl} -vf fps=fps=1/5 -update 1 storage/app/public/channelsImages/{$streamId}.jpg > /dev/null 2>&1 & echo $!;");
        return intval($pid);
    }

    /**
     * funkce na ukoncení všech náhledů
     *
     * @return array
     */
    public static function kill_running_ffmpegs()
    {

        if (Stream::where('ffmpeg_pid', "!=", null)->first()) {

            // existují streamy, kterým běží na pozadí ffmpeg
            foreach (Stream::where('ffmpeg_pid', "!=", null)->get() as $ffmpegToKill) {
                StreamController::stop_diagnostic_stream_from_backend($ffmpegToKill->ffmpeg_pid);

                //  update záznamu
                Stream::where('id', $ffmpegToKill->id)->update(['ffmpeg_pid' => null]);
            }

            return [
                'status' => "success",
                'message' => "Ukončeny všechny tvorby náhledů!"
            ];
        } else {

            return [
                'status' => "success",
                'message' => "Momentálně není co killnout!"
            ];
        }
    }
}
