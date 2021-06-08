<?php

namespace App\Http\Controllers;

use App\Events\StreamImage;
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
     *
     * @param string $streamId
     * @param string $streamUrl
     * @return void
     */
    public static function find_image_if_exist_delete_and_create_new(string $streamId, string $streamUrl, string $oldImage): void
    {
        $newImgName = $streamId . microtime(true) . '.jpg';
        // $imgName = $streamId . ".jpg";

        $stream = Stream::find($streamId);

        $streamUrl = trim($stream->stream_url);
        // vyhledání stávajícího náhledu, a případné smazání

        if (file_exists(public_path($stream->image))) {
            // Náhled existuje => odebrání náhledu z filesystemu
            unlink(public_path($stream->image));

            $stream->update(['image' => 'false']);
        }

        //  vytvoření náhledu
        shell_exec("timeout -s SIGKILL 20 ffmpeg -ss 3 -i udp://{$streamUrl} -vframes:v 1 storage/app/public/channelsImages/{$newImgName} -timeout 15");

        // kontrola, zda se náhled zkutečně vytvořil

        if (file_exists(public_path("storage/channelsImages/{$newImgName}"))) {
            $stream->update(['image' => "storage/channelsImages/{$newImgName}"]);

            // odeslání eventu do frontendu
            event(new StreamImage($streamId, "storage/channelsImages/{$newImgName}"));
        }
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
            Stream::where('ffmpeg_pid', "!=", null)->get()->each(function ($ffmpegToKill) {

                StreamController::stop_diagnostic_stream_from_backend($ffmpegToKill->ffmpeg_pid);
                //  update záznamu
                Stream::where('id', $ffmpegToKill->id)->update(['ffmpeg_pid' => null]);
            });

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
