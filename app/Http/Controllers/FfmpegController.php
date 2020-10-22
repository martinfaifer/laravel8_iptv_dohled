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
        $streamUrl = trim($streamUrl);
        // vyhledání stávajícího náhledu, a případné smazání
        if (file_exists(public_path($oldImage))) {
            // Náhled existuje => odebrání náhledu z filesystemu
            // return "existuje";
            unlink(public_path($oldImage));

            Stream::where('id', $streamId)->update(['image' => 'false']);
        }

        //  vytvoření náhledu
        shell_exec("timeout --foreground 10s ffmpeg -i {$streamUrl} -vframes 3 storage/app/public/channelsImages/{$newImgName} -timeout 10 -timelimit 9");


        // kontrola, zda se náhled zkutečně vytvořil

        if (file_exists(public_path("storage/channelsImages/{$newImgName}"))) {
            Stream::where('id', $streamId)->update(['image' => "storage/channelsImages/{$newImgName}"]);

            // odeslání eventu do frontendu
            event(new StreamImage($streamId, "storage/channelsImages/{$newImgName}"));
        }

        // pokud bude existoval, update záznamu, a spustení eventu, pro odeslání do frontendu do mozaiky



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
