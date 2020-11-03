<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamHistory;
use Illuminate\Http\Request;

class StreamHistoryController extends Controller
{
    /**
     * funkce pro výpis posledních 10 záznamů do timeline v streamInfo
     *
     * @param Request $request->streamId
     * @return void
     */
    public function stream_info_history_ten(Request $request)
    {
        // vyhledání zda existuje nějaký záznam se streamId
        if (StreamHistory::where('stream_id', $request->streamId)->first()) {
            foreach (StreamHistory::where('stream_id', $request->streamId)->orderBy('id', 'desc')->take(10)->get() as $streamHistory) {
                $created_at = explode(".", $streamHistory['created_at']);
                if ($streamHistory['status'] == 'stream_ok') {
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
            return "none";
        }
    }


    /**
     * fn pro zobrazení posledních 10 událostí
     *
     * @return void
     */
    public function return_last_10_history()
    {
        if (StreamHistory::orderBy('id', 'desc')->take(10)->first()) {
            foreach (StreamHistory::orderBy('id', 'desc')->take(10)->get() as $history) {
                $data[] = array(
                    'nazev' => Stream::where('id', $history->stream_id)->first()->nazev,
                    'data' => $history->status,
                    'created_at' => substr($history->created_at, 0, 10)
                );
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
}
