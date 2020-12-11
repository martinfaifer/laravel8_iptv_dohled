<?php

/**
 *
 * ----------------------------------------------------------------------------------------------------------------------------------------------
 * DIAGNOSTICKÉ JÁDRO, KTERÉ ZODPOVÍDÁ ZA KONTROLU KANÁLŮ , POUŽÍVÁ SE PRIMÁRNĚ TSDUCK PRO DIAGNOSTIKU A FFPROBE PRO ZÍSKÁNÍ LOW LEVEL INFORMACÍ
 * --------------------------------------------------------------------------------------------------------------------------------------------
 *
 * JÁDRO VERZE 1.0 RC
 *
 * change log
 * upravena ntifikace kanálu, kdy se neměnil záznam v historie
 *
 *
 */

namespace App\Http\Controllers;

use App\Events\StreamInfoAudioBitrate;
use App\Events\StreamInfoCa;
use App\Events\StreamInfoService;
use App\Events\StreamInfoTs;
use App\Events\StreamInfoTsVideoBitrate;
use App\Jobs\Diagnostic_Status_Update;
use App\Jobs\Diagnostic_Stream_update;
use App\Jobs\StreamNotificationLimit;
use App\Models\CcError;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\StopedStream;
use App\Models\Stream;
use App\Models\StreamHistory;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;
use React\EventLoop\Factory;


class DiagnosticController extends Controller
{

    /**
     * funkce pro prevedeni stringu, ktery generuje tsduck do pole
     *
     * @param string $tsduckString
     * @return array
     */
    public static function convert_tsduck_string_to_array(string $tsduckString): array
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
                 * -----------------------------------------------
                 * SERVICE => OBECNÉ INFORMACE O TRANSPORT STREAMU
                 * -----------------------------------------------
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
     * pro ukonceni je znám pid processu, který se následně killne
     *
     * @param string $streamUrl
     * @param string $streamId
     * @return void
     */
    public static function stream_realtime_diagnostic_and_return_status(string $streamUrl, string $streamId): void
    {
        try {
            Log::info("stream {$streamId} je spusten");

            /**
             * ---------------------------------------------------------------------------------------------------------------------------------------
             * EVENT LOOP
             * vše co je v eventLoop , funguje async => rychlejší odbavení / zpracování dat bez nutnosti cekání, I/O no blocking
             * ---------------------------------------------------------------------------------------------------------------------------------------
             */
            $eventLoop = Factory::create();

            $eventLoop->addPeriodicTimer(1, function () use ($streamId, $streamUrl) {
                $ts_data = null;
                $global_data = null;
                $pids_data = null;
                // overení zda stream nemá status stop
                // získání informací o streamu
                if ($streamInfoData = Stream::where('id', $streamId)->first()) {
                    if (!StopedStream::where('streamId', "!=", $streamId)->first()) {

                        // spuštění tsducku pro diagnostiku kanálu ip = multicast http = hls
                        $tsDuckData = shell_exec("timeout -s SIGKILL 3 tsp -I ip {$streamUrl} -P until -s 1 -P analyze --normalized -O drop");

                        // zde neco co mi bude hlídat, zda tsDuckData se rádně ukoncil a zda vrátil nějakou hodnotu

                        // ověření zda analýza selhala či nikoliv
                        if (is_null($tsDuckData) || $tsDuckData === "Killed") {

                            // analýza selhala, kanál nejspíše není funkční
                            // kanál nyní označíme jako nefunkční, aktualizujeme status kanálu a následně uložíme do historie, pro budoucí výpis
                            // před aktualizací statusu, oveření zda kanál již posledním uloženým statusem není označen jako nefunkční

                            // FFprobeController::ffprobe_diagnostic($streamUrl, $streamId, null);
                        } else if (empty($tsDuckData)) {
                            // prádná data => stream nefunguje
                            if ($streamInfoData->status != "error") {
                                Stream::where('id', $streamId)->update(['status' => "error"]);

                                StreamHistory::create([
                                    'stream_id' => $streamId,
                                    'status' => "stream_error"
                                ]);

                                // založení do tabulky channels_which_waiting_for_notifications, pokud jiz neexistuje
                                // overení, zda stream má povolenou notifikaci
                                if ($streamInfoData->sendMailAlert == true) {
                                    if (!ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->first()) {
                                        // vytvorení záznamu
                                        ChannelsWhichWaitingForNotification::create([
                                            'stream_id' => $streamId,
                                            'whenToNotify' => date("Y-m-d H:i", strtotime('+5 minutes'))
                                        ]);
                                    }
                                }
                            }
                        }
                        // stream funguje
                        // vyčtou se ze streamu všechna možná data, která případně pomohou diagnostikovat chyby ve streamu
                        // převod stringu do pole
                        else {
                            // fn pro převedení stringu do pole
                            $tsduckArr = self::convert_tsduck_string_to_array($tsDuckData);

                            // overení, ze se jedná skotecne o pole
                            if (is_array($tsduckArr)) {
                                // kanál není ve statusu error, dojde k vyhledání, zda existuje v tabulce channels_which_waiting_for_notifications a odebrání
                                if (ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->first()) {
                                    // odebrání záznamu z tabulky
                                    ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->delete();
                                }

                                // zpracování pole
                                // vyhledání specifických klíčů, dle kterých se pole zpracuje
                                if (array_key_exists('ts', $tsduckArr)) {
                                    // pokud je vse v poradku vraci pole "status" => "success"
                                    // pokud bude jakakoliv chyba ,  raci pole "status" => "issue" , "dataAlert" => ["streamId" => $streamId, "status" => "status", "message" => "message"]
                                    $ts_data = self::collect_transportStream_from_tsduckArr($tsduckArr["ts"], $streamId);
                                }

                                // kontrola, zda pid není roven 0 , pokud ano, vypadá to, že stream je bez audia
                                // vytvoření podminky, kdy se nemá kontrolovat zvuk u videa
                                if ($streamInfoData->dohledVolume == true) {
                                    if (array_key_exists('global', $tsduckArr)) {
                                        // pokud je vse v poradku vraci pole "status" => "success"
                                        // pokud bude jakakoliv chyba ,  raci pole "status" => "issue" , "dataAlert" => ["streamId" => $streamId, "status" => "status", "message" => "message"]
                                        $global_data = self::collect_global_from_tsduckArr($tsduckArr["global"], $streamId);
                                    }
                                } else {
                                    $global_data = [
                                        'status' => "success"
                                    ];
                                }

                                // zobrazení servisních informací
                                if (array_key_exists('service', $tsduckArr)) {
                                    self::collect_service_from_tsduckArr($tsduckArr["service"], $streamId);
                                }


                                // array || null video, array || null audio, array || null ca
                                // tato funkce je nejdulezitejsi z cele diagnostiky
                                if (array_key_exists('pids', $tsduckArr)) {
                                    $pids = self::collect_pids_from_tsduckArr($tsduckArr["pids"]);

                                    // Zpracování pidů
                                    // od teto analýzy se odvíjí téměř veškeré informace o streamu

                                    // pokud je vse v poradku vraci pole "status" => "success"
                                    // pokud bude jakakoliv chyba ,  raci pole "status" => "issue" , "dataAlert" => ["streamId" => $streamId, "status" => "status", "message" => "message"]
                                    $pids_data = self::analyze_pids_and_storeData($pids, $streamId, $streamUrl);
                                }

                                // analyzování výstupu z jednotlivých podruzných funkcí
                                // nesmí nic být hodnoty null
                                if (!is_null($ts_data) && !is_null($global_data) && !is_null($pids_data)) {
                                    if (is_array($ts_data) && is_array($global_data) && is_array($pids_data)) {
                                        if ($ts_data["status"] != "success" || $global_data["status"] != "success" || $pids_data["status"] != "success") {

                                            // update záznamu na issue
                                            if (!StopedStream::where('streamId', $streamId)->first()) {
                                                if ($streamInfoData->status != "issue") {
                                                    // ZMĚNA STAVU Z SUCCESS NEBO ERROR NA ISSUE
                                                    dispatch(new Diagnostic_Stream_update($streamId, "issue"));
                                                }

                                                // zpracování alertů
                                                // vyhledání zda existuje v poly klic "dataAlert"
                                                if (array_key_exists("dataAlert", $ts_data)) {
                                                    dispatch(new Diagnostic_Status_Update($streamId, $ts_data["dataAlert"]));
                                                }

                                                if (array_key_exists("dataAlert", $pids_data)) {
                                                    dispatch(new Diagnostic_Status_Update($streamId, $pids_data["dataAlert"]));
                                                }

                                                if (array_key_exists("dataAlert", $global_data)) {
                                                    dispatch(new Diagnostic_Status_Update($streamId, $global_data["dataAlert"]));
                                                }
                                            } else {
                                                dispatch(new Diagnostic_Stream_update($streamId, "stop"));
                                            }
                                        } else {
                                            if (!StopedStream::where('streamId', $streamId)->first()) {
                                                // NEBYLA NALEZENA ŽÁDNÁ CHYBA, ZMĚNA STAVU Z ISSUE NEBO ERROR NA SUCCESS
                                                if ($streamInfoData->status != "success") {
                                                    // UPDATE ZÁZNAMU
                                                    dispatch(new Diagnostic_Stream_update($streamId, "success"));
                                                }
                                            } else {
                                                dispatch(new Diagnostic_Stream_update($streamId, "stop"));
                                            }
                                        }
                                        unset($tsDuckData);
                                        unset($global_data);
                                        unset($tsduckArr);
                                        unset($ts_data);
                                        unset($pids);
                                        unset($pids_data);
                                    }
                                } else {
                                    unset($tsDuckData);
                                    unset($global_data);
                                    unset($tsduckArr);
                                    unset($ts_data);
                                    unset($pids);
                                    unset($pids_data);
                                }
                            }
                        }
                    }
                }
            });

            $eventLoop->run();
        } catch (\Throwable $th) {
            Log::error("{$streamId} => $th");
        }
    }

    /**
     * funkce, která z analyzuje veské informace o pidech
     *
     * analýza probíhá u videa , audio a ca
     *
     *
     * Pokud se vyskytne u nejakeho pidu informace jiná nez access => "clear" ==> pid se nedekryptuje a jedná se o problém v multicastu ( nejvetsi problem vzniká pokud je tato informace u videa, kdy není funkční stream )
     * Informace scrambled určuje kolik packetů je poškozených, ideální je hodnota "0"
     * bitrate je v jednotkách bps => převod na Mbps
     * discontinuities nutné aby bylo "0"
     * description jedná se pouze o popis
     * pid nese id pidu
     * language slouží pro informaci o jazykové stope u audia
     *
     * výše uvedené informace jsou ve valné většině dostupné u videa , audia a ca
     *
     *
     * @param array $pids
     * @param string $streamId
     * @return array
     */
    public static function analyze_pids_and_storeData(array $pids, string $streamId, string $streamUrl): array
    {

        $videoBitrate = "";
        $audioBitrate = "";
        // zpracování videa
        // kontrola statusu access
        // zaznamenání video datového toku , scrambled, discontinuities
        // pokud proměnná pids['video'] není null budeme zpracovávat
        // pokud by byla null tak se můwže jednat o možnou chybu nebo stream je pouze audio, což se týká radio kanálů => tuto informaci musí nést kanál u sebe
        if (!is_null($pids['video'])) {
            // vyhledání klíče access a vyhodnocení
            if ($pids['video']['access'] != "clear") {
                // pokud hodnota není rovná "clear" => video se netranscoduje
                $videoAccess = "error";
            } else {
                // video se transcoduje
                $videoAccess = "success";
            }

            // získání hodnoty bitrate
            // pokud bude video bitrate = 0 nejspíše chyba
            if (array_key_exists("bitrate", $pids['video'])) {
                $videoBitrate = $pids['video']['bitrate'];
            } else {
                $videoBitrate = "1";
            }

            if (array_key_exists("pid", $pids['video'])) {
                $videoPid = $pids['video']['pid'];
            } else {
                $videoPid = "0";
            }

            if (array_key_exists("discontinuities", $pids['video'])) {
                $discontinuities = $pids['video']['discontinuities'];
            } else {
                $discontinuities = "0";
            }
            if (array_key_exists("scrambled", $pids['video'])) {
                $scrambled = $pids['video']['scrambled'];

                if ($pids['video']['discontinuities'] != "0") {
                    CcError::create([
                        'streamId' => $streamId,
                        'ccError' => $pids['video']['discontinuities'],
                        'pozition' => "video"
                    ]);
                }
            } else {
                $scrambled = "0";
            }

            if (array_key_exists("videoDescription", $pids['video'])) {
                $videoDescription = $pids['video']['videoDescription'];
            } else {
                $videoDescription = "bez popisu";
            }
        }


        // zpracování audia
        // pokud proměnná pids['audio'] není null budeme zpracovávat
        // pokud by byla null tak se můwže jednat o možnou chybu nebo stream je pouze audio, což se týká radio kanálů => tuto informaci musí nést kanál u sebe
        if (!is_null($pids['audio'])) {
            // vyhledání klíče access a vyhodnocení
            if ($pids['audio']['access'] != "clear") {
                // pokud hodnota není rovná "clear" => audio se netranscoduje
                $audioAccess = "error";
            } else {
                // audio se transcoduje
                $audioAccess = "success";
            }

            // získání hodnoty bitrate
            if (array_key_exists("bitrate", $pids['audio'])) {
                $audioBitrate = $pids['audio']['bitrate'];
            } else {
                $audioBitrate = "1";
            }
            if (array_key_exists("pid", $pids['audio'])) {
                $audioPid = $pids['audio']['pid'];
            } else {
                $audioPid = "0";
            }

            if (array_key_exists("discontinuities", $pids['audio'])) {
                $audioDiscontinuities = $pids['audio']['discontinuities'];
                if ($pids['audio']['discontinuities'] != "0") {
                    CcError::create([
                        'streamId' => $streamId,
                        'ccError' => $pids['audio']['discontinuities'],
                        'pozition' => "audio"
                    ]);
                }
            } else {
                $audioDiscontinuities = "0";
            }
            if (array_key_exists("scrambled", $pids['audio'])) {
                $audioScrambled = $pids['audio']['scrambled'];
            } else {
                $audioScrambled = "0";
            }
            if (array_key_exists("language", $pids['audio'])) {
                $audioLanguage = $pids['audio']['language'];
            } else {
                $audioLanguage = "language_not_detected";
            }
        }


        // zpracování CA

        // pokud proměnná $pids['ca'] není null budeme zpracovávat
        // pokud by byla null tak se může jednat o možnou chybu nebo stream je pouze audio, což se týká radio kanálů => tuto informaci musí nést kanál u sebe
        if (!is_null($pids['ca'])) {
            // vyhledání klíče access a vyhodnocení
            if ($pids['ca']['access'] != "clear") {
                // pokud hodnota není rovná "clear" => audio se netranscoduje
                $caAccess = "error";
            } else {
                // audio se transcoduje
                $caAccess = "success";
            }

            // získání hodnot
            $caDescription = $pids['ca']['description'];
            $caScrambled = $pids['ca']['scrambled'];
        }

        /**
         * ---------------------------------------------------------------------------
         * ověření, že je co ukládat do tabulky StreamVideo
         * ---------------------------------------------------------------------------
         */
        if (isset($videoPid) && isset($videoAccess) && isset($discontinuities) && isset($scrambled) && isset($videoBitrate)) {

            event(new StreamInfoTsVideoBitrate($streamId, $videoBitrate, $videoPid, $discontinuities, $scrambled, $videoAccess, $videoDescription));
        }

        /**
         * ---------------------------------------------------------------------------
         * ověření, že je co ukládat do tabulky StreamAudio
         * ---------------------------------------------------------------------------
         */

        if (isset($audioBitrate) && isset($audioPid) && isset($audioDiscontinuities) && isset($audioScrambled) && isset($audioLanguage) && isset($audioAccess)) {

            // cache audio pole
            // $cache_audio = array(
            //     'pid' => (int) $audioPid,
            //     'access' => $audioAccess,
            //     'discontinuities' => $audioDiscontinuities,
            //     'scrambled' => $audioScrambled,
            //     'language' => $audioLanguage,
            //     'bitrate' => $audioBitrate
            // );

            if (array_key_exists("description", $pids['audio'])) {
                $audioDescription = $pids['audio']['description'];
            } else {
                $audioDescription = "";
            }

            event(new StreamInfoAudioBitrate($streamId, $audioBitrate, $audioPid, $audioDiscontinuities, $audioScrambled, $audioLanguage, $audioAccess, $audioDescription));

            // Dispatch JOB pouze pokud hodnoty jsou jiné než 0 až na videoBitrate
            if ($videoBitrate == "0" || $discontinuities != "0" || $scrambled != "0" ||  $audioDiscontinuities != "0" || $audioScrambled != "0") {
                // dispatch Job StreamNotificationLimit
                dispatch(new StreamNotificationLimit($streamId, $videoBitrate, $discontinuities, $scrambled, $audioDiscontinuities, $audioScrambled));
            }
        }



        /**
         * ---------------------------------------------------------------------------
         * ověření, že je co ukládat do tabulky StreamCa
         * ---------------------------------------------------------------------------
         */

        if (isset($caScrambled) && isset($caAccess)) {

            // $cache_ca = array(
            //     'description' => $caDescription ?? null,
            //     'access' => $caAccess,
            //     'scrambled' => $caScrambled
            // );

            // prostor pro vebsocket
            event(new StreamInfoCa($streamId, $caDescription ?? null, $caAccess, $caScrambled));
        }



        /**
         * ---------------------------------------------------------------------------------------------------------------------------
         * Kontrola $audioAccess , $videoAccess => pokud neco z toho bude mít jiny status nez clear ===> PROBLEM
         *
         * možnost u stream vytvořit výjimku na dohled jednolivých procesů
         *
         * pokud stream nemá vytvořenou výjimku na dohled
         * ---------------------------------------------------------------------------------------------------------------------------
         */

        //  uložení informace o streamu do proměnné
        $streamData = Stream::where('id', $streamId)->first();

        // pokud streamData->dohledVidea && streamData->dohledAudia jsou obojí true , zpracuje se vše jak je vidět zde dole
        if ($streamData->dohledVidea == true && $streamData->dohledVolume == true) {

            // kontrola zda existují hodnoty
            if (isset($audioAccess) && isset($videoAccess)) {
                if ($audioAccess == 'success' && $videoAccess == 'success') {

                    // overení, ze kanál má hodnotu success
                    if ($streamData->status != 'success') {
                        // Stream::where('id', $streamId)->update(['status' => "success"]);
                        return [
                            'status' => "success"
                        ];
                    }
                } else {
                    // Stream::where('id', $streamId)->update(['status' => "issue"]);
                    return [
                        'status' => "issue",
                        'dataAlert' => array([
                            'status' => "no_dekrypt",
                            'message' => "audio nebo video se nedekryptuje"
                        ])
                    ];
                }
            }
        } else if (isset($videoBitrate) && isset($audioBitrate)) {
            if ($videoBitrate == "0" || $audioBitrate == "0") {
                // video nebo audio bitrate je = 0
                if ($videoBitrate == "0") {
                    return [
                        'status' => "issue",
                        'dataAlert' => array([
                            'status' => "no_video_bitrate",
                            'message' => "Video má nulový datový tok!"
                        ])
                    ];
                } else {
                    return [
                        'status' => "issue",
                        'dataAlert' => array([
                            'status' => "no_audio_bitrate",
                            'message' => "Audio má nulový bitrate!"
                        ])
                    ];
                }
            } else {
                return [
                    'status' => "success"
                ];
            }

            // pokud se nedohleduje video ( radio kanál )
        } else if ($streamData->dohledVidea == false && $streamData->dohledVolume == true) {
            if (isset($audioAccess)) {
                if ($audioAccess == 'success') {
                    // overení, ze kanál má hodnotu success
                    return [
                        'status' => "success"
                    ];
                } else {
                    return [
                        'status' => "issue",
                        'dataAlert' => array([
                            'status' => "no_dekrypt",
                            'message' => "audio se nedekryptuje"
                        ])
                    ];
                }
            }

            // dohleduje se video, ale ne audio
        } else if ($streamData->dohledVidea == true && $streamData->dohledVolume == false) {
            if (isset($videoAccess)) {
                if ($videoAccess == 'success') {
                    // overení, ze kanál má hodnotu success
                    return [
                        'status' => "success"
                    ];
                } else {
                    return [
                        'status' => "issue",
                        'dataAlert' => array([
                            'status' => "no_dekrypt",
                            'message' => "video se nedekryptuje"
                        ])
                    ];
                }
            }
        } else {
            return [
                'status' => "success"
            ];
        }
    }


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
    public static function collect_transportStream_from_tsduckArr(array $tsduckArr, string $streamId): array
    {
        // definice proměnných
        $invalidsyncs = null;
        $scrambledpids = null;
        $transporterrors = null;
        $counrty = null;

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

            if (Str::contains($ts, 'country=')) {
                // zpracování pokud existuje
                $counrty = str_replace('country=', "", $ts);
            }
        }

        event(new StreamInfoTs($streamId, $counrty));

        // pokud je hodnota jiná u invalidsyncs , scrambledpids , transporterrors jiná nez 0
        if ($invalidsyncs == "0" && $transporterrors == "0") {

            return [
                'status' => "success"
            ];
        } else {

            // update statusu na issue

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

    /**
     * funkce pro získání service dat tsid , access=clear , pmtpid , pcrpid , provider , name
     *
     * @param array $tsduckArr
     * @param string $streamId
     * @return void
     */
    public static function collect_service_from_tsduckArr(array $tsduckArr, string $streamId): void
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
        }

        event(new StreamInfoService($streamId, $tsid, $pmtpid, $pcrpid, $name, $provider));
    }

    /**
     * funkce na zpracování pidu, které jsme získali z tsducku
     *
     *
     * Postupné zopracování jednotlivých pidů v následujícím pořadí ( video , audio , CA )
     *
     * video obsahuje pid, discontinuities , description ,  bitrate , access , scrambled
     * audio obsahuje pid , access , language , bitrate , scrambled , discontinuities , description
     * ca obsahuje $caPidDataArray, zpracování
     *
     * @param array $tsduckArr
     * @return array video array || null
     * @return array audio array || null
     * @return array
     */
    public static function collect_pids_from_tsduckArr(array $tsduckArr): array
    {

        $videoDescription = null;
        $audioDescription = null;
        $videoBitrate = "";
        // zpracování pole "pids", kdy se bere pid a data v něm
        foreach ($tsduckArr as $pid) {

            foreach ($pid as $dataInsidePid) {
                if (Str::contains($dataInsidePid, 'video')) {
                    // pokud existuje klic video, vrátí mí hodnotu $pid, kterou se reuložíme do proměnné videoPid
                    $videoPid = $pid;
                }

                if (Str::contains($dataInsidePid, 'audio')) {
                    // pokud existuje klic audio, vrátí mí hodnotu $pid, kterou se reuložíme do proměnné audioPid
                    $audioPid[] = $pid;
                }

                if (Str::contains($dataInsidePid, 'ecm')) {
                    // pokud existuje klic ecm, vrátí mí hodnotu $pid, kterou se reuložíme do proměnné caPid
                    $caPid[] = $pid;
                }
            }
        }

        // zpracování video pidu

        // pokud neexistuje $videoPid ==> vytvoření $videoPid = null
        $videoPid = $videoPid ?? null;
        if (!is_null($videoPid)) {

            foreach ($videoPid as $videoPidData) {
                // pid, discontinuities , description ,  bitrate , access , scrambled

                // vyhledání pid
                if (Str::contains($videoPidData, 'pid=')) {

                    $videoPidStr = str_replace('pid=', "", $videoPidData);
                }

                // vyhledání didiscontinuities
                if (Str::contains($videoPidData, 'discontinuities=')) {

                    $videoDiscontinuities = str_replace('discontinuities=', "", $videoPidData);
                }

                // vyhledání dedescription
                if (Str::contains($videoPidData, 'description=')) {

                    $videoDescription = str_replace('description=', "", $videoPidData);
                }

                // vyhledání bitrate
                if (Str::contains($videoPidData, 'bitrate=')) {

                    $videoBitrate = str_replace('bitrate=', "", $videoPidData);
                }


                // vyhledání scrambled
                if (Str::contains($videoPidData, 'scrambled=')) {

                    $videoScrambled = str_replace('scrambled=', "", $videoPidData);
                }

                // vyhledání scrambled
                if (Str::contains($videoPidData, 'access=')) {

                    $videoAccess = str_replace('access=', "", $videoPidData);
                }

                if (Str::contains($videoPidData, 'description=')) {

                    $videoDescription = str_replace('description=', "", $videoPidData);
                }
            }
            $videoPidOutputData = [
                'pid' => $videoPidStr,
                'discontinuities' => $videoDiscontinuities,
                'description' => $videoDescription,
                'bitrate' => $videoBitrate,
                'scrambled' => $videoScrambled,
                'access' => $videoAccess,
                'videoDescription' => $videoDescription
            ];
        } else {

            $videoPidOutputData = $videoPid;
        }


        // zpracování audio pidu

        // pokud neexistuje $audioPid ==> vytvoření $audioPid = null
        $audioPid = $audioPid ?? null;
        if (!is_null($audioPid)) {
            foreach ($audioPid as $audioPidDataArr) {
                $str = implode(",", $audioPidDataArr);

                if (Str::contains($str, "language=cze")) {
                    // vytviření pole pro kontrolu
                    $czeAudioArr = $audioPidDataArr;
                }
            }

            if (isset($czeAudioArr)) {
                // pid , access , language , bitrate , scrambled , discontinuities , description
                $audioPidOutputData = self::separate_from_audio_pids($czeAudioArr);
            } else {

                $audioPidOutputData = self::separate_from_audio_pids($audioPidDataArr);
            }

            // vytvoření jednorozměrného pole
            $audioPidOutputData = Arr::collapse($audioPidOutputData);
        } else {

            $audioPidOutputData = $audioPid;
        }

        // zpracování caPidu

        // pokud neexistuje $caPid ==> vytvoření $caPid = null
        $caPid = $caPid ?? null;
        if (!is_null($caPid)) {
            // scrambled , access , description
            foreach ($caPid as $caPidDataArray) {

                // pole $caPidDataArray, zpracování
                foreach ($caPidDataArray as $caPidData) {

                    if (Str::contains($caPidData, 'scrambled=')) {
                        $caPidOutputData[] = array(
                            'scrambled' => str_replace('scrambled=', '', $caPidData)
                        );
                    }

                    if (Str::contains($caPidData, 'access=')) {
                        $caPidOutputData[] = array(
                            'access' => str_replace('access=', '', $caPidData)
                        );
                    }

                    if (Str::contains($caPidData, 'description=')) {
                        $caPidOutputData[] = array(
                            'description' => str_replace('description=', '', $caPidData)
                        );
                    }
                }
            }

            // z vícerozměrného pole se vytvotvoří 1d pole
            $caPidOutputData = Arr::collapse($caPidOutputData);
        } else {
            $caPidOutputData = $caPid;
        }


        // vrácení hodnot do hlavní funknce, která jej následně zpracuje a vyhodnotí
        return [
            'video' => $videoPidOutputData,
            'audio' => $audioPidOutputData,
            'ca' => $caPidOutputData
        ];
    }


    /**
     * fn pro vytazení informací z pole audio/idsArray, které má detailní informace o streamu
     *
     * získává se pid, access ( dekryptace ) , language, bitrate, scrambled, discontinuity, description
     *
     * @param array $audioPidsArray
     * @return array
     */
    public static function separate_from_audio_pids(array $audioPidsArray): array
    {
        foreach ($audioPidsArray as $audioPidData) {
            // vytvoreni pole
            if (Str::contains($audioPidData, 'pid=')) {
                $audioPidOutputData[] = array(
                    'pid' => str_replace('pid=', '', $audioPidData)
                );
            }
            if (Str::contains($audioPidData, 'access=')) {
                $audioPidOutputData[] = array(
                    'access' => str_replace('access=', '', $audioPidData)
                );
            }
            if (Str::contains($audioPidData, 'language=')) {
                $audioPidOutputData[] = array(
                    'language' => str_replace('language=', '', $audioPidData)
                );
            }
            if (Str::contains($audioPidData, 'bitrate=')) {
                $audioPidOutputData[] = array(
                    'bitrate' => str_replace('bitrate=', '', $audioPidData)
                );
            }
            if (Str::contains($audioPidData, 'scrambled=')) {
                $audioPidOutputData[] = array(
                    'scrambled' => str_replace('scrambled=', '', $audioPidData)
                );
            }
            if (Str::contains($audioPidData, 'discontinuities=')) {
                $audioPidOutputData[] = array(
                    'discontinuities' => str_replace('discontinuities=', '', $audioPidData)
                );
            }
            if (Str::contains($audioPidData, 'description=')) {
                $audioPidOutputData[] = array(
                    'description' => str_replace('description=', '', $audioPidData)
                );
            }
        }

        return $audioPidOutputData;
    }
}
