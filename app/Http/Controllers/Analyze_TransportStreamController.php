<?php

namespace App\Http\Controllers;

use App\Events\StreamInfoTs;
use Illuminate\Support\Str;

class Analyze_TransportStreamController extends Controller
{

    /**
     * funkce pro získání invalidsyncs , scrambledpids , transporterrors
     *
     * pokud bude vše v poradku, vrací success
     *
     * pokud bude nejaka chyba, vraci pole chyb, pro následne ulození v mainfunkci
     *
     * @param array $tsduckArr
     * @param string $streamId
     * @return void
     */
    public static function collect_transportStream_from_tsduckArr(array $tsduckArrTransportStreamData, string $streamId): array
    {
        // definice proměnných
        $invalidsyncs = null;
        $transporterrors = null;
        $country = null;

        // vyhledání dat
        foreach ($tsduckArrTransportStreamData as $ts) {

            if (Str::contains($ts, 'invalidsyncs=')) {
                // zpracování
                $invalidsyncs = str_replace('invalidsyncs=', "", $ts);
            }

            if (Str::contains($ts, 'transporterrors=')) {
                // zpracování
                $transporterrors = str_replace('transporterrors=', "", $ts);
            }

            if (Str::contains($ts, 'country=')) {
                // zpracování pokud existuje
                $country = str_replace('country=', "", $ts);
            }
        }

        // websocket 
        event(new StreamInfoTs($streamId, $country));

        if ($invalidsyncs === "0" && $transporterrors === "0") {
            return [
                'status' => "success"
            ];
        }

        // u kazdé hodnoty overení zda jiz existuje chyba
        // pokud chyba bude existovat , vynechá se

        // ulození jednotlivých chyb do tabulky stream_alerts
        if ($invalidsyncs != "0") {

            $outputErrStats[] = array(
                'stream_id' => $streamId,
                'status' => "invalidSync_warning",
                'message' => "Desynchronizace Audia / videa"
            );
        }

        if ($transporterrors != "0") {
            $outputErrStats[] = array(
                'stream_id' => $streamId,
                'status' => "transporterrors_warning",
                'message' => "Zobrazila se TS chyba"
            );
        }

        return [
            'status' => "issue",
            "dataAlert" => $outputErrStats
        ];
    }
}
