<?php

namespace App\Http\Controllers\Streams;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Streams\StreamHistoryController;
use App\Http\Controllers\Notifications\StreamSheduleFromIptvDokuController;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Traits\CacheTrait;

class StreamInfoController extends Controller
{
    use CacheTrait;
    /**
     * funkce, která získá odkaz na náhled, status kanálu
     *
     * @param Request $request->streamId
     * @return void
     */
    public function stream_info_image(Request $request)
    {
        if ($stream = Stream::where('id', $request->streamId)->first()) {

            // stream s id $request->streamId existuje
            return [
                'nazev' => $stream['nazev'],
                'status' => $stream['status'],
                'image' => $stream["image"],
                'is_problem' => $stream['is_problem']
            ];
        } else {
            redirect(404);
        }
    }

    /**
     * funkce na vrácení status streamu
     *
     * @param Request $request
     * @return array
     */
    public static function stream_info_checkStatus(Request $request): array
    {
        $stream = Stream::find($request->streamId);
        if ($stream) {

            if (Cache::has("stream" . $stream->id)) {
                $streamInCache =  Cache::get("stream" . $stream->id);
                return [
                    'status' => $streamInCache['status'],
                    'streamName' => $stream->nazev,
                    'start_time' => $stream->start_time
                ];
            }

            return [
                'status' => $stream->status,
                'streamName' => $stream->nazev,
                'start_time' => $stream->start_time
            ];
        }
        return [
            'status' => 'not_found'
        ];
    }


    /**
     * funkce na získání detailních informací o streamu z Cache
     *
     * @param Request $request->streamId
     * @return array
     */
    public function stream_info_detail(Request $request): array
    {

        $video = null;
        $audio = null;
        $ca = null;

        // vyhledání záznamu z cache pro video
        if (Cache::has($request->streamId . "_video")) {
            $video = Cache::get($request->streamId . "_video");
        }

        // cache pro audio
        if (Cache::has($request->streamId . "_audio")) {
            $audio = Cache::get($request->streamId . "_audio");
        }

        // cache pro ca
        if (Cache::has($request->streamId . "_ca")) {
            $ca = Cache::get($request->streamId . "_ca");
        }

        return [
            'audio' => $audio,
            'video' => $video,
            'ca' => $ca
        ];
    }

    /**
     * funkce na získání informací z dokumentace pomocí api
     * stream musí mít vyplněnou uri v poli dokumentaceUrl
     *
     * pokud stream nemá tuto informaci vyplněnou, vrátí se "none" string
     *
     * pokud stream neexistuje redirect 404
     *
     * @param Request $request
     * @return array | redirect
     */
    public function stream_info_doku(Request $request)
    {
        // vyhledání zda existuje streamId
        if ($stream = Stream::where('id', $request->streamId)->first()) {

            if (is_null($stream['dokumentaceUrl'])) {
                return [
                    'status' => "none",
                    'message' => "Stream nemá povolené API"
                ];
            }

            // zavolání funkce z RemoteApiController
            return;
        } else {

            return [
                'status' => "none",
                'message' => "Stream neexistuje"
            ];
        }
    }

    public function show_audio_video_bitrate_data(Request $request): array
    {
        try {
            for ($i = 300; $i > 1; $i--) {
                if (Cache::has($request->streamId . '_audio_bitrate_' . now()->subSeconds($i)->format('H:i:s'))) {

                    $cache = Cache::get($request->streamId . '_audio_bitrate_'  . now()->subSeconds($i)->format('H:i:s'));

                    $seriesData_audio[] = $cache['value'];
                    $xaxis[] = $cache['created_at'];
                    $seriesData_audio_scrambled[] = $cache['scrambled'];
                    $seriesData_audio_discontinuity[] = $cache['discontinuity'];
                    $audio_pid = $cache['pid'];
                }

                if (Cache::has($request->streamId . '_video_bitrate_' . now()->subSeconds($i)->format('H:i:s'))) {

                    $cache = Cache::get($request->streamId . '_video_bitrate_'  . now()->subSeconds($i)->format('H:i:s'));

                    $seriesData_video[] = $cache['value'];
                    $seriesData_video_scrambled[] = $cache['scrambled'];
                    $seriesData_video_discontinuity[] = $cache['discontinuity'];
                    $video_pid = $cache['pid'];
                }
            }

            if (isset($xaxis)) {
                $output = [
                    array(
                        'name' => "audio bitrate v Kbps - PID " . $audio_pid,
                        'data' => $seriesData_audio
                    ),
                    array(
                        'name' => "video bitrate v Kbps - PID " . $video_pid,
                        'data' => $seriesData_video
                    ),
                ];

                $scrambled = [
                    array(
                        'name' => "počet chyb v PIDu " . $audio_pid,
                        'data' => $seriesData_audio_scrambled
                    ),
                    array(
                        'name' => "počet chyb v PIDu " . $video_pid,
                        'data' => $seriesData_video_scrambled
                    ),
                    array(
                        'name' => "počet poškozených paketů v PIDu " . $audio_pid,
                        'data' => $seriesData_audio_discontinuity
                    ),
                    array(
                        'name' => "počet poškozených paketů v PIDu " . $video_pid,
                        'data' => $seriesData_video_discontinuity
                    ),
                ];

                return [
                    'status' => "exist",
                    'xaxis' => $xaxis,
                    'seriesData' => $output,
                    'seriesDataScrambled' => $scrambled
                ];
            }

            return [
                'status' => "empty"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error"
            ];
        }
    }

    public function get_shedule_data(Request $request): array
    {
        return StreamSheduleFromIptvDokuController::return_shedule_data($request);
    }

    public function get_today_shedule_data(Request $request): array
    {
        return StreamSheduleFromIptvDokuController::check_if_today_is_shedule($request);
    }

    public function get_history_data(Request $request): mixed
    {
        return StreamHistoryController::stream_info_history($request);
    }

    public function show_stream_pids_scrambled_data(Request $request)
    {
        $video = $this->show_scrambled($request->streamId . "_video_scrambled");
        $audio = $this->show_scrambled($request->streamId . "_audio_scrambled");

        return [
            'video_scrambled' => $video,
            'audio_scrambled' => $audio
        ];
    }
}
