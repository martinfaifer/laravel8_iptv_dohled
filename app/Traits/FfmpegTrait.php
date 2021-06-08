<?php

namespace App\Traits;

use App\Http\Controllers\StreamHistoryController;
use Illuminate\Support\Facades\Cache;

trait FfmpegTrait
{
    public static function ffmpeg_create_image(string $streamUrl): void
    {
        // 
    }

    public static function ffmpeg_analyze(string $streamUrl): array
    {
        return [];
    }

    public static function ffprobe(string $streamUrl): array
    {
        $ffprobeOutput = shell_exec("timeout -s SIGKILL 3 ffprobe -v quiet -print_format json -show_entries stream=bit_rate -show_programs {$streamUrl} -timeout 1");

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
                if (Cache::has("stream" . $streamId)) {
                    // odebrání z cache
                    Cache::pull("stream" . $streamId);
                    // zapsání do historie -> stream_ok
                    StreamHistoryController::create($streamId, "stream_ok");
                }
            } else {

                $checkVideo = intval($video_start_time) - intval($start_time);
                $checkAudio = intval($audio_start_time) - intval($start_time);

                if ($checkVideo <= 1 &&  $checkAudio <= 1) {
                    // v toleranci => success
                    if (Cache::has("stream" . $streamId)) {
                        // odebrání z cache
                        Cache::pull("stream" . $streamId);
                        // zapsání do historie -> stream_ok
                        StreamHistoryController::create($streamId, "stream_ok");
                    }
                } else {
                    // AV resync
                    if (!Cache::has("stream" . $streamId)) {
                        Cache::put("stream" . $streamId, 'Audio video resync!');
                        StreamHistoryController::create($streamId, 'stream_out_of_sync');
                    }
                }
            }
        }
    }
}
