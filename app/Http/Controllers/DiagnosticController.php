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
     * funkce pro prevedeni stringu, ktery generuje tsduck do pole
     *
     * @param string $tsduckString
     * @return array
     */
    public static function convert_tsduck_string_to_array(string $tsduckString)
    {
        // definice proměnné, do které se budou ukladat zpracovaná data ze stringu
        $output = array();
        //vytvoření pole, které obsahuje zatím pro nás nepotřebné informace o streamu, toto pole se následne zpracuje pro nás ideální formu
        $tsDuckData = explode("\n", $tsduckString);

        foreach ($tsDuckData as $data) {
            // title: vynecháváme jelikož pro ás je to zbytečný udaj
            if ($data == "title:") {
                /**
                 * -------------------------------------------------------------
                 * NIC ZDE NEDĚLÁME A POKRACUJEME VESELE DÁLE VE ZPRACOVÁNÍ POLE
                 * -------------------------------------------------------------
                 */
            } else {

                /**
                 * ---------------------------------------------------
                 * TS => TRANSPORT STREAM, OBECNÉ INFORMACE O STREAMU
                 * ---------------------------------------------------
                 */
                if (Str::contains($data, "ts:")) {
                    $data = str_replace("ts:", "", $data);
                    $output["ts"] = explode(":", $data);
                }

                /**
                 * ------------------------------------------
                 * GLOBAL => OBSAHUJE PIDY, UNICAST A POD
                 * ------------------------------------------
                 */

                if (Str::contains($data, "global:")) {
                    $data = str_replace("global:", "", $data);
                    $output["global"] = explode(":", $data);
                }

                /**
                 * --------------------------------------------
                 * SERVICE => OBECNÉ INFORMACE PODOBNĚ JAKO TS
                 * --------------------------------------------
                 */
                if (Str::contains($data, "service:")) {
                    $data = str_replace("service:", "", $data);
                    $output["service"] = explode(":", $data);
                }

                /**
                 * ------------------------------------------
                 * PIDS => INFORMACE O JEDNOTLIVÝCH PIDECH
                 *
                 * JEDNÁ SE O VÍCEROZMĚRNÉ POLE
                 * ------------------------------------------
                 */

                if (Str::contains($data, "pid:")) {
                    $data = str_replace("pid:", "", $data);
                    $pids[] = explode(":", $data);
                    $output["pids"] = $pids;
                }
            }
        }

        return $output;
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
            // převod stringu do pole
            else {
                // fn pro převedení stringu do pole
                $tsduckArr = self::convert_tsduck_string_to_array($tsDuckData);


                // zpracování pole
                // vyhledání specifických klíčů, dle kterých se pole zpracuje
                if (array_key_exists('ts', $tsduckArr)) {
                    $ts = self::collect_transportStream_from_tsduckArr($tsduckArr["ts"]);
                }

                if (array_key_exists('global', $tsduckArr)) {
                    $global = self::collect_global_from_tsduckArr($tsduckArr["global"]);
                }

                if (array_key_exists('service', $tsduckArr)) {
                    $service = self::collect_service_from_tsduckArr($tsduckArr["service"]);
                }

                if (array_key_exists('pids', $tsduckArr)) {
                    $pids = self::collect_pids_from_tsduckArr($tsduckArr["pids"]);
                }
            }
        }
    }


    /**
     * funkce pro získání invalidsyncs , scrambledpids , transporterrors
     *
     * @param array $tsduckArr
     * @return array
     */
    public static function collect_transportStream_from_tsduckArr(array $tsduckArr)
    {
        // definice proměnných
        $invalidsyncs = null;
        $scrambledpids = null;
        $transporterrors = null;

        // vyhledání dat
        foreach ($tsduckArr as $ts) {

            if (Str::contains($ts, 'invalidsyncs=')) {
                // zpracování
                $invalidsyncs = str_replace('invalidsyncs=', "", $ts);
            }
            if (Str::contains($ts, 'scrambledpids=')) {
                // zpracování
                $scrambledpids = str_replace('scrambledpids=', "", $ts);
            }

            if (Str::contains($ts, 'transporterrors=')) {
                // zpracování
                $transporterrors = str_replace('transporterrors=', "", $ts);
            }
        }

        // poslání na zpět do hlavní funkce
        return [
            $invalidsyncs,
            $scrambledpids,
            $transporterrors
        ];
    }

    /**
     * funknce pro získání celkového bitratu "bitrate"
     *
     * @param array $tsduckArr
     * @return array
     */
    public static function collect_global_from_tsduckArr(array $tsduckArr)
    {

        // definice proměnných
        $maxBitrate = null;

        // vyhledání dat
        foreach ($tsduckArr as $global) {

            if (Str::contains($global, 'bitrate=')) {
                // zpracování
                $maxBitrate = str_replace('bitrate=', "", $global);
            }
        }

        // poslání na zpět do hlavní funkce
        return [
            $maxBitrate
        ];
    }

    /**
     * funkce pro získání service dat tsid , access=clear , pmtpid , pcrpid , provider , name
     *
     * @param array $tsduckArr
     * @return array
     */
    public static function collect_service_from_tsduckArr(array $tsduckArr)
    {
        // definice proměnných
        $tsid = null;
        $access = null;
        $pmtpid = null;
        $pcrpid = null;
        $provider = null;
        $name = null;

        // vyhledání dat
        foreach ($tsduckArr as $service) {

            if (Str::contains($service, 'tsid=')) {
                // zpracování
                $tsid = str_replace('tsid=', "", $service);
            }

            if (Str::contains($service, 'pmtpid=')) {
                // zpracování
                $pmtpid = str_replace('pmtpid=', "", $service);
            }

            if (Str::contains($service, 'pcrpid=')) {
                // zpracování
                $pcrpid = str_replace('pcrpid=', "", $service);
            }

            if (Str::contains($service, 'provider=')) {
                // zpracování
                $provider = str_replace('provider=', "", $service);
            }

            if (Str::contains($service, 'name=')) {
                // zpracování
                $name = str_replace('name=', "", $service);
            }

            // nejslozitejsi cast!!!
            // pokud existuje access=clear => tak se kanál jako takový dekryptuje
            if (Str::contains($service, 'access=clear')) {
                // zpracování
                $access = "access";
            } else {
                $access = "scrambled";
            }
        }

        // poslání na zpět do hlavní funkce
        return [
            $tsid,
            $pmtpid,
            $pcrpid,
            $provider,
            $name,
            $access
        ];
    }



    // "pids": [
    //     [
    //     "pid=0",
    //     "access=clear",
    //     "servcount=0",
    //     "bitrate=6009",
    //     "bitrate204=6520",
    //     "packets=4",
    //     "clear=4",
    //     "scrambled=0",
    //     "invalidscrambling=0",
    //     "af=0",
    //     "pcr=0",
    //     "discontinuities=0",
    //     "duplicated=0",
    //     "unitstart=4",
    //     "description=PAT"
    //     ],
    //     [
    //     "pid=17",
    //     "access=clear",
    //     "servcount=0",
    //     "bitrate=1502",
    //     "bitrate204=1629",
    //     "packets=1",
    //     "clear=1",
    //     "scrambled=0",
    //     "invalidscrambling=0",
    //     "af=0",
    //     "pcr=0",
    //     "discontinuities=0",
    //     "duplicated=0",
    //     "unitstart=1",
    //     "description=SDT/BAT"
    //     ],
    //     [
    //     "pid=20",
    //     "access=clear",
    //     "servcount=0",
    //     "bitrate=1502",
    //     "bitrate204=1629",
    //     "packets=1",
    //     "clear=1",
    //     "scrambled=0",
    //     "invalidscrambling=0",
    //     "af=0",
    //     "pcr=0",
    //     "discontinuities=0",
    //     "duplicated=0",
    //     "unitstart=1",
    //     "description=TDT/TOT"
    //     ],
    //     [
    //     "pid=1100",
    //     "pmt",
    //     "access=clear",
    //     "servcount=1",
    //     "servlist=601",
    //     "bitrate=24039",
    //     "bitrate204=26084",
    //     "packets=16",
    //     "clear=16",
    //     "scrambled=0",
    //     "invalidscrambling=0",
    //     "af=0",
    //     "pcr=0",
    //     "discontinuities=0",
    //     "duplicated=0",
    //     "unitstart=16",
    //     "description=PMT"
    //     ],
    //     [
    //     "pid=1101",
    //     "access=clear",
    //     "streamid=224",
    //     "video",
    //     "servcount=1",
    //     "servlist=601",
    //     "bitrate=11149955",
    //     "bitrate204=12098887",
    //     "packets=7421",
    //     "clear=7421",
    //     "scrambled=0",
    //     "invalidscrambling=0",
    //     "af=55",
    //     "pcr=28",
    //     "discontinuities=0",
    //     "duplicated=0",
    //     "pes=25",
    //     "invalidpesprefix=0",
    //     "description=HEVC video"
    //     ],
    //     [
    //     "pid=1102",
    //     "access=clear",
    //     "streamid=192",
    //     "audio",
    //     "language=eng",
    //     "servcount=1",
    //     "servlist=601",
    //     "bitrate=282467",
    //     "bitrate204=306506",
    //     "packets=188",
    //     "clear=188",
    //     "scrambled=0",
    //     "invalidscrambling=0",
    //     "af=47",
    //     "pcr=0",
    //     "discontinuities=0",
    //     "duplicated=0",
    //     "pes=47",
    //     "invalidpesprefix=0",
    //     "description=MPEG-2 AAC Audio (eng, Audio layer 0, dual channel)"
    //     ],
    //     [
    //     "pid=1191",
    //     "ecm",
    //     "cas=2816",
    //     "access=clear",
    //     "servcount=1",
    //     "servlist=601",
    //     "bitrate=0",
    //     "bitrate204=0",
    //     "packets=0",
    //     "clear=0",
    //     "scrambled=0",
    //     "invalidscrambling=0",
    //     "af=0",
    //     "pcr=0",
    //     "discontinuities=0",
    //     "duplicated=0",
    //     "unitstart=0",
    //     "description=Conax ECM"
    //     ]
    //     ]


    /**
     * funkce na zpracování pidu, které jsme získali z tsducku
     *
     *
     * tato funknce schromazduje veskere informace co maji pidy jako jsou audio / video pidy, ale take pidy modulu a krypt
     *
     * zasila se pole, deleni se jeste musi promyslet ...
     *
     * @param array $tsduckArr
     * @return array
     */
    public static function collect_pids_from_tsduckArr(array $tsduckArr)
    {
    }
}
