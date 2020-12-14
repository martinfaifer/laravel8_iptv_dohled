<?php

namespace App\Http\Controllers;

use App\Models\EmailStats;
use Illuminate\Http\Request;

class EmailStatsController extends Controller
{
    public function create_dayly_stats(): array
    {
        // overeni, ze existuje nejaký záznam
        if (!EmailStats::fist()) {
            return [
                'status' => "empty"
            ];
        }
        foreach (EmailStats::orderBy('created_at', "<=", now()->subDay(1))->get() as $stat) {
            return [];
        }

        return [];
    }
}


// foreach (SystemHistory::where('value_type', "streams")->get() as $streamsDataHistory) {
//     $seriesData[] = $streamsDataHistory->value;
//     $xaxis[] = substr($streamsDataHistory->created_at, 0, 19);
// }

// return [
//     'status' => "exist",
//     'xaxis' => $xaxis,
//     'seriesData' => $seriesData
// ];
// } else {
// return [
//     'status' => "empty"
// ];
// }