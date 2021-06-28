<?php

namespace App\Traits;

use App\Events\StreamImage;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\StreamHistoryController;
use App\Jobs\RestartStreamJob;
use App\Models\Stream;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait FfmpegTrait
{
    public static function ffmpeg_create_image(object $stream): void
    {
        $newImgName = $stream->id . microtime(true) . '.jpg';

        $streamUrl = trim($stream->stream_url);

        if (file_exists(public_path($stream->image))) {
            unlink(public_path($stream->image));
            $stream->update(['image' => 'false']);
        }

        //  vytvoření náhledu
        if (Str::contains($streamUrl, "http")) {
            shell_exec("timeout -s SIGKILL 20 ffmpeg -ss 3 -i {$streamUrl} -vframes:v 1 storage/app/public/channelsImages/{$newImgName} -timeout 15");
        } else {
            shell_exec("timeout -s SIGKILL 20 ffmpeg -ss 3 -i udp://{$streamUrl} -vframes:v 1 storage/app/public/channelsImages/{$newImgName} -timeout 15");
        }

        // kontrola, zda se náhled zkutečně vytvořil
        if (file_exists(public_path("storage/channelsImages/{$newImgName}"))) {
            $stream->update(['image' => "storage/channelsImages/{$newImgName}"]);

            event(new StreamImage($stream->id, "storage/channelsImages/{$newImgName}"));
        }
    }

    public static function ffprobe(string $streamUrl): array
    {
        $ffprobeOutput = shell_exec("timeout -s SIGKILL 10 ffprobe -v quiet -print_format json -show_entries stream=bit_rate -show_programs {$streamUrl} -timeout 1");

        return match ($ffprobeOutput) {
            is_null($ffprobeOutput) => [],
            empty($ffprobeOutput) => [],
            'Killed' => [],
            default => json_decode($ffprobeOutput, true)
        };
    }

    public static function check_if_stream_is_resync_audio_video(array $ffprobeOutput, int $streamId): void
    {
        $start_time = null;
        $video_start_time = null;
        $audio_start_time = null;

        if (array_key_exists('programs', $ffprobeOutput)) {
            foreach ($ffprobeOutput["programs"] as $program) {
                if (array_key_exists("start_time", $program)) {
                    $start_time = round($program["start_time"], 0);
                }

                foreach ($ffprobeOutput["programs"][0]["streams"] as $streams) {
                    if ($streams["codec_type"] == "video") {

                        if (array_key_exists("start_time", $streams)) {
                            $video_start_time = round($streams["start_time"], 0);
                        }
                    }

                    if ($streams["codec_type"] == "audio") {
                        if (array_key_exists("start_time", $streams)) {
                            $audio_start_time = round($streams["start_time"], 0);
                        }
                    }
                }
            }
        }

        self::analyze_audio_video_start_times($start_time, $video_start_time, $audio_start_time, $streamId);
    }

    protected static function analyze_audio_video_start_times($start_time, $video_start_time, $audio_start_time, int $streamId): void
    {
        if (!is_null($start_time) && !is_null($video_start_time) && !is_null($audio_start_time)) {
            if ($start_time === $video_start_time && $start_time === $audio_start_time) {

                if (Cache::has("stream" . $streamId . "_resync")) {
                    // odebrání z cache
                    Cache::pull("stream" . $streamId . "_resync");
                    // zapsání do historie -> stream_ok
                    StreamHistoryController::create($streamId, "stream_audio_ok");
                }
            } else {

                $stream = Stream::find($streamId);

                $checkVideo = intval($video_start_time) - intval($start_time);
                $checkAudio = intval($audio_start_time) - intval($start_time);

                if ($checkVideo <= 1 &&  $checkAudio <= 1) {
                    // v toleranci => success
                    if (Cache::has("stream" . $streamId . "_resync")) {
                        // odebrání z cache
                        Cache::pull("stream" . $streamId . "_resync");
                        // zapsání do historie -> stream_ok
                        StreamHistoryController::create($streamId, "stream_audio_ok");
                    }
                } else {
                    // AV resync
                    if (!Cache::has("stream" . $streamId . "_resync")) {
                        Cache::put("stream" . $streamId . '_resync', [
                            'status' => "issue",
                            'stream' => $stream->nazev,
                            'msg' => "Audio video resync!"
                        ]);
                        RestartStreamJob::dispatch($streamId);
                        // ZAVOLÁNÍ FN PRO RESTART STREAMU
                        StreamHistoryController::create($streamId, 'stream_out_of_sync');
                    }
                }
            }
        }
    }
}
