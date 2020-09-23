<?php

/**
 *
 * ----------------------------------------------------------------------------------------------------------------------------------------------
 * DIAGNOSTICKÉ JÁDRO, KTERÉ ZODPOVÍDÁ ZA KONTROLU KANÁLŮ , POUŽÍVÁ SE PRIMÁRNĚ TSDUCK PRO DIAGNOSTIKU A FFPROBE PRO ZÍSKÁNÍ LOW LEVEL INFORMACÍ
 * ----------------------------------------------------------------------------------------------------------------------------------------------
 *
 *
 */

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StreamHistory;

class DiagnosticController extends Controller
{

    /**
     * fn pro získání invalidsyncs ze záznamu tsducku
     *
     * @param array $streamData
     * @return string
     */
    public static function collect_invalidsync_from_diagnostic(array $streamData)
    {

        foreach ($streamData as $data) {
            // pokud existuje invalidsyncs= vrátí hodnotu
            if (!Str::contains($data, "invalidsyncs=")) {
                return null;
            }

            return str_replace("invalidsyncs=", "", $data);
        }
    }


    /**
     * fn pro získání TS erroru
     *
     * @param array $streamData
     * @return string
     */
    public static function collect_transportErrors_from_diagnostic(array $streamData)
    {

        foreach ($streamData as $data) {
            // pokud existuje invalidsyncs= vrátí hodnotu
            if (!Str::contains($data, "transporterrors=")) {
                return null;
            }

            return str_replace("transporterrors=", "", $data);
        }
    }

    /**
     * fn pro zsíkání pcrPidu
     *
     * @param array $streamData
     * @return void
     */
    public static function collect_pcrpid_from_diagnostic(array $streamData)
    {

        foreach ($streamData as $data) {
            // pokud existuje invalidsyncs= vrátí hodnotu
            if (!Str::contains($data, "pcrpid=")) {
                return null;
            }

            return str_replace("pcrpid=", "", $data);
        }
    }


    /**
     * hlavní funkce. která v realném čase dohleduje jednotlivé streamy
     *
     * @param array $stream
     * @return void
     */
    public static function stream_realtime_diagnostic_and_return_status(array $stream)
    {
        $loop = "start";
        while ($loop == "start") {
            // spuštění tsducku pro diagnostiku kanálu
            $tsDuckData = shell_exec("tsp -I http {$stream["url"]} -P until -s 1 -P analyze --normalized -O drop");

            // ověření zda analýza selhala či nikoliv
            if (empty($tsDuckData)) {

                // analýza selhala, kanál nejspíše není funkční
                // kanál nyní označíme jako nefunkční, aktualizujeme status kanálu a následně uložíme do historie, pro budoucí výpis hysorie
                // před aktualizací statusu, oveření zda kanál již posledním uloženým statusem není označen jako nefunkční

                if (!StreamHistory::where('stream_id', $stream["id"])->latest()->first()->status == false) {
                    // poslední status u kanálu není false ( nefunkční ), založí se nový status
                    StreamHistory::create([
                        'stream_id' => $stream["id"],
                        'status' => false
                    ]);
                }
            }

            // stream funguje
            // vyčtou se ze streamu všechny možná data, která případně pomohou diagnostikovat chyby ve streamu
            else {

                // tsduck posílá neformátovaný string, který je zapotřebý kompletně rozebrat a vyčíst si data co nás zajímají pro případnou diagnostiku a dohled
                // hodnoty co nás zajímají -> invalidsyncs ( ověření zda video / audio jsou synchronní ) , transporterrors ( hlídání počtu chyb ve streamu  ) , bitrate ( sledování datového toku ) , access=scrambled ( jelikož v tomto případě je hodnota scrambled => video se nedekryptuje již na příjmu)

                // převedení stringu do pole rozdělením podle ":" pod stejnou proměnnou
                $tsDuckData = explode(":", $tsDuckData);

                // zpracování dat a následně navrácení potřebných hodnot
                $loop = \React\EventLoop\Factory::create();

                self::collect_invalidsync_from_diagnostic($tsDuckData); // invalidsync
                self::collect_transportErrors_from_diagnostic($tsDuckData); // transportErrors
                self::collect_pcrpid_from_diagnostic($tsDuckData); // pcrpid

                $loop->run();
            }
        }
    }
}
