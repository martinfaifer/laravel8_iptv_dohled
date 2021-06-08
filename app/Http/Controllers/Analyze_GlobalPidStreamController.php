<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class Analyze_GlobalPidStreamController extends Controller
{
    /**
     * uéskání globalních dat, pro nevyužitelnost vrací success
     *
     * @param array $tsduckArr
     * @param string $streamId
     * @return void
     */
    public static function collect_global_from_tsduckArr(array $tsduckArr, string $streamId): array
    {
        return [
            'status' => "success"
        ];
    }
}
