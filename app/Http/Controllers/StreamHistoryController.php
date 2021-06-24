<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamHistory;
use Illuminate\Http\Request;

class StreamHistoryController extends Controller
{

    public static function create(int $streamId, string $status): void
    {
        StreamHistory::create([
            'stream_id' => $streamId,
            'status' => $status
        ]);
    }

    /**
     * funkce pro výpis posledních 10 záznamů do timeline v streamInfo
     *
     * @param Request $request->streamId
     * @return void
     */
    public function stream_info_history(Request $request)
    {
        // vyhledání zda existuje nějaký záznam se streamId
        if (StreamHistory::where('stream_id', $request->streamId)->first()) {
            foreach (StreamHistory::where('stream_id', $request->streamId)->orderBy('id', 'desc')->take($request->records)->get() as $streamHistory) {

                $created_at = explode(".", $streamHistory['created_at']);

                $historie[] = match ($streamHistory['status']) {
                    'stream_ok' => [
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "green",
                        'created_at' => $created_at[0]
                    ],
                    'stream_start' => [
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "green",
                        'created_at' => $created_at[0]
                    ],
                    'stream_audio_ok' => [
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "green",
                        'created_at' => $created_at[0]
                    ],
                    'stream_stoped_by_user' => [
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "red",
                        'created_at' => $created_at[0]
                    ],
                    'sheduler_disable' => [
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "green",
                        'created_at' => $created_at[0]
                    ],
                    'sheduler_enable' => [
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "green",
                        'created_at' => $created_at[0]
                    ],
                    'issue' => [
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "orange",
                        'created_at' => $created_at[0]
                    ],
                    default => [
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "red",
                        'created_at' => $created_at[0]
                    ]
                };
            }
            return $historie;
        } else {
            return "none";
        }
    }



    /**
     * fn pro zobrazení posledních 10 událostí
     *
     * @return void
     */
    public function streams_history(int $records)
    {

        $data = [];

        if (StreamHistory::first()) {
            foreach (StreamHistory::orderBy('id', 'desc')->take($records)->get() as $history) {
                if (Stream::where('id', $history->stream_id)->first()) {
                    if ($history->status === "stream_ok") {
                        $data[] = array(
                            'id' => $history->id,
                            'nazev' => Stream::where('id', $history->stream_id)->first()->nazev,
                            'status' => $history->status,
                            'color' => "success",
                            'created_at' => substr($history->created_at, 0, 19)
                        );
                    } else if ($history->status === "stream_start") {
                        $data[] = array(
                            'id' => $history->id,
                            'nazev' => Stream::where('id', $history->stream_id)->first()->nazev,
                            'status' => $history->status,
                            'color' => "success",
                            'created_at' => substr($history->created_at, 0, 19)
                        );
                    } else if ($history->status === "stream_audio_ok") {
                        $data[] = array(
                            'id' => $history->id,
                            'nazev' => Stream::where('id', $history->stream_id)->first()->nazev,
                            'status' => $history->status,
                            'color' => "success",
                            'created_at' => substr($history->created_at, 0, 19)
                        );
                    } else {
                        $data[] = array(
                            'id' => $history->id,
                            'nazev' => Stream::where('id', $history->stream_id)->first()->nazev,
                            'status' => $history->status,
                            'color' => "error",
                            'created_at' => substr($history->created_at, 0, 19)
                        );
                    }
                }
            }

            return $data;
        } else {
            return [
                'status' => "empty"
            ];
        }
    }


    /**
     * funkce pro výpis posledních 10 záznamů do timeline v streamInfo
     *
     * @param string streamId
     * @return array
     */
    public static function stream_info_history_ten_for_events($streamId)
    {
        // vyhledání zda existuje nějaký záznam se streamId
        if (StreamHistory::where('stream_id', $streamId)->first()) {
            foreach (StreamHistory::where('stream_id', $streamId)->orderBy('id', 'desc')->take(10)->get() as $streamHistory) {
                $created_at = explode(".", $streamHistory['created_at']);
                if ($streamHistory['status'] == 'stream_ok') {
                    $historie[] = array(
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "green",
                        'created_at' => $created_at[0]
                    );
                } else if ($streamHistory['status'] == 'sheduler_disable') {
                    $historie[] = array(
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "green",
                        'created_at' => $created_at[0]
                    );
                } else if ($streamHistory['status'] == 'sheduler_enable') {
                    $historie[] = array(
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "green",
                        'created_at' => $created_at[0]
                    );
                } else if ($streamHistory['status'] == 'issue') {
                    $historie[] = array(
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "orange",
                        'created_at' => $created_at[0]
                    );
                } else {
                    $historie[] = array(
                        'id' => $streamHistory['id'],
                        'status' => $streamHistory['status'],
                        'color' => "red",
                        'created_at' => $created_at[0]
                    );
                }
            }
            return $historie;
        } else {
            return [];
        }
    }


    /**
     * fn pro odebrání záznamů starších než 12h z tabulky stream_history
     *
     * @return void
     */
    public static function get_last_twelve_hours_records_last_delete(): void
    {
        if (StreamHistory::where('created_at', '<=', now()->subHours(12))->first()) {
            StreamHistory::where('created_at', '<=', now()->subHours(12))->delete();
        }
    }
}
