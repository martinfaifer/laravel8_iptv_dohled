<?php

namespace App\Traits;

use App\Http\Controllers\Streams\StreamHistoryController;
use Illuminate\Support\Facades\Cache;

trait CacheTrait
{
    public static function delete_all_stream_cache(string $streamId): void
    {
        Cache::pull("stream" . $streamId . "_resync");
        Cache::pull("stream" . $streamId);
        Cache::pull($streamId . "_video_scrambled");
        Cache::pull($streamId . "_audio_scrambled");
    }

    public static function remove_becouse_stream_is_success(object $stream): void
    {
        if (Cache::has("stream" . $stream->id)) {
            // odebrání z cache
            Cache::pull("stream" . $stream->id);
            // zapsání do historie -> stream_ok

            StreamHistoryController::create($stream->id, "stream_ok");

            $stream->update([
                'is_problem' => false
            ]);
        }
    }

    public static function store_stream_is_not_start(object $stream): void
    {
        if (!Cache::has("stream" . $stream->id)) {
            Cache::put("stream" . $stream->id, [
                'status' => "error",
                'stream' => $stream->nazev,
                'msg' => "Nepodařilo se spustit stream"
            ]);
            StreamHistoryController::create($stream->id, "start_error");
            $stream->update([
                'status' => "start_error"
            ]);
        }
    }

    public static function store_becouse_stream_is_not_success(object $stream, array $errorData): void
    {
        if (!Cache::has("stream" . $stream->id)) {

            Cache::put("stream" . $stream->id, [
                'status' => "issue",
                'stream' => $stream->nazev,
                'msg' => $errorData["msg"]
            ]);
            StreamHistoryController::create($stream->id, $errorData["alert_status"]);

            $stream->update([
                'is_problem' => true
            ]);
        }
    }

    public static function store_becouse_stream_is_killed(object $stream): void
    {
        if (!Cache::has("stream" . $stream->id)) {

            Cache::put("stream" . $stream->id, [
                'status' => "error",
                'stream' => $stream->nazev,
                'msg' => "stream_ko"
            ]);
            StreamHistoryController::create($stream->id, "stream_without_signal");
            $stream->update([
                'is_problem' => true,
                'start_time' => null
            ]);
        }
    }

    public static function store_scrambled($cache_key, $scrambled): void
    {
        if (Cache::has($cache_key)) {
            $cache_scrambled = Cache::get($cache_key);
            Cache::pull($cache_key);
            Cache::put($cache_key, [
                'scrambled' => $scrambled + $cache_scrambled['scrambled']
            ]);
        } else {
            Cache::put($cache_key, [
                'scrambled' => $scrambled
            ]);
        }
    }

    public static function show_scrambled(string $cache_key): int
    {
        if (Cache::has($cache_key)) {
            $cache = Cache::get($cache_key);

            return $cache['scrambled'];
        }
        return 0;
    }

    public static function store_pid_bitrate_scrambled_and_discontinuity($key, $bitrate, $scrambled, $discontinuity, $pid): void
    {
        Cache::put($key, [
            'value' => $bitrate,
            'scrambled' => $scrambled,
            'discontinuity' => $discontinuity,
            'pid' => $pid,
            'created_at' => date('H:i:s')
        ], now()->addMinutes(5));
    }
}
