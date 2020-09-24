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

use App\Models\Stream;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StreamHistory;
use App\Models\StreamSync;

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
     * @return string
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
     * fn pro získání informací zda se dekryptuje kanál
     *
     * @param array $streamData
     * @return string
     */
    public static function collect_dekryptInfo_from_diagnostic(array $streamData)
    {

        // access=scrambled
        // access=clear

        foreach ($streamData as $data) {
            // pokud existuje hodnota scrambled => kanál se nedekryptuje ==> PROBLEM NA MULTICASTU
            // overeni zda vubec existuje scrambpled nebo access
            if (!Str::contains($data, "access=scrambled") || !Str::contains($data, "access=clear")) {
                return null;
            }

            // pokud existuje access=scrambled ==> MULTICAST PROBLEM
            if (Str::contains($data, 'access=scrambled')) {

                return "no_dekrypt";
            }

            return "dektypt"; // kanál se dekryptuje, je vše v pořádku
        }
    }


    /**
     * hlavní funkce. která v realném čase dohleduje jednotlivé streamy
     * u kanálu je uložen process_pid pod kterým funguje tento loop
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

                    // aktualizace záznamu v tabulce streams
                    Stream::where('id', $stream["id"])->update(['status', "error"]);
                }
            }

            // stream funguje
            // vyčtou se ze streamu všechny možná data, která případně pomohou diagnostikovat chyby ve streamu
            else {

                // tsduck posílá neformátovaný string, který je zapotřebý kompletně rozebrat a vyčíst si data co nás zajímají pro případnou diagnostiku a dohled
                // hodnoty co nás zajímají -> invalidsyncs ( ověření zda video / audio jsou synchronní ) , transporterrors ( hlídání počtu chyb ve streamu  ) , bitrate ( sledování datového toku ) , access=scrambled ( jelikož v tomto případě je hodnota scrambled => video se nedekryptuje již na příjmu)

                // převedení stringu do pole rozdělením podle ":" pod stejnou proměnnou
                $tsDuckData = explode(":", $tsDuckData);

                // zpracování dat a následně navrácení potřebných hodnot , data se sbírají asynchronně
                $reactLoop = \React\EventLoop\Factory::create();

                // sběr dat a následné vyhodnocení
                $invalidSync = self::collect_invalidsync_from_diagnostic($tsDuckData); // invalidsync
                $transportErrors = self::collect_transportErrors_from_diagnostic($tsDuckData); // transportErrors
                $pcrPid = self::collect_pcrpid_from_diagnostic($tsDuckData); // pcrpid
                $dekrypt = self::collect_dekryptInfo_from_diagnostic($tsDuckData); // scrambled info ( zjisteni zda se kanal dekryptuje ci nikoliv)

                $reactLoop->run();

                // ověření zda stream má hodnotu null
                // pokud má hodnotu null tak je něco špatně ve streamu jelikož se jedná o elementární hodnoty, které stream musí obsahovat. tudíž stream nejspíše nefunguje tak jak by měl
                // uloží se do StreamHistory jako false jelikoz stream nefunguje korektně
                if (is_null($invalidSync) || is_null($transportErrors) || is_null($pcrPid) || is_null($dekrypt)) {

                    StreamHistory::create([
                        'stream_id' => $stream["id"],
                        'status' => false
                    ]);


                    //  zárověn se stream aktualizje v tabulce streams , kdy se změní status ze jineho než warning na warning
                    // ověření zda stream je ve stavu warrning
                    if (Stream::find($stream["id"])->status != "warning") {

                        // aktualizace záznamu v tabulce streams
                        Stream::where('id', $stream["id"])->update(['status', "warrning"]);
                    }
                } else {

                    // Ověření zda kanál má problém se synchronizací audia / videa
                    // pro test ukládání do tabulky, následně po vyhodnocení jak se data ukladají a co všechno se zaznamenává se nad tímto provede diagnostika
                    StreamSync::create([
                        'stream_id' => $stream["id"],
                        'sync_data' => $invalidSync
                    ]);

                    // Ověřením zda se stream dekryptuje
                    // pokud se nedekryptuje aktualizuje se záznam u kanálu NO_SCRAMBLED no_dekrypt

                    if ($dekrypt == "no_dekrypt") {
                        Stream::where('id', $stream["id"])->update(['status', "NO_SCRAMBLED"]);
                    } else if ($pid = Stream::find($stream["id"])->pcrPid == null) {
                        // pcrpid => hlídání zda se změnil ci nikoliv
                        // pokud se změní je nutné provést změnu stavu v tabulce streams
                        // pokud pcrpid jeste neexistuje v tabulce streams => zalození updatem
                        Stream::where('id', $stream["id"])->updade(['pcrPid', $pcrPid]);
                    } else if ($pid != $pcrPid) {

                        // pidy nejsou stejne, nejspise doslo ke zmene poradi primo od distributora
                        Stream::where('id', $stream["id"])->update(['status', "pid_not_match"]);
                    } else {


                        // vse je v porádku, overení zda kanal jiz je ve statusu success
                        if (Stream::find($stream["id"])->status != "success") {

                            // aktualizace záznamu v tabulce streams
                            Stream::where('id', $stream["id"])->update(['status', "success"]);
                        }
                    }
                }
            }
        }
    }
}
