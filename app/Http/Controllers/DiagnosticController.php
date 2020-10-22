<?php

/**
 *
 * ----------------------------------------------------------------------------------------------------------------------------------------------
 * DIAGNOSTICKÉ JÁDRO, KTERÉ ZODPOVÍDÁ ZA KONTROLU KANÁLŮ , POUŽÍVÁ SE PRIMÁRNĚ TSDUCK PRO DIAGNOSTIKU A FFPROBE PRO ZÍSKÁNÍ LOW LEVEL INFORMACÍ
 * --------------------------------------------------------------------------------------------------------------------------------------------
 *
 * JÁDRO VERZE 0.8
 *
 *
 */

namespace App\Http\Controllers;

use App\Events\StreamInfoAudioBitrate;
use App\Events\StreamInfoHistory;
use App\Events\StreamInfoTsVideoBitrate;
use App\Events\StreamNotification;
use App\Jobs\FFprobeDiagnostic;
use App\Jobs\SendSuccessEmail;
use App\Jobs\StreamInfoTsVideoBitrateJob;
use App\Models\AudioBitrate;
use App\Models\ChannelsWhichWaitingForNotification;
use App\Models\Stream;
use App\Models\StreamAlert;
use App\Models\StreamAudio;
use App\Models\StreamBitrate;
use App\Models\StreamCa;
use Illuminate\Support\Str;
use App\Models\StreamHistory;
use App\Models\StreamService;
use App\Models\StreamTSdata;
use App\Models\StreamVideo;
use App\Models\VideoBitrate;
use Illuminate\Support\Arr;
use React\EventLoop\Factory;

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
    public static function stream_realtime_diagnostic_and_return_status(string $streamUrl, string $streamId)
    {
        $loop = "start";
        while ($loop == "start") {
            // získání informací o streamu
            $streamInfoData = Stream::where('id', $streamId)->first();

            // $eventLoop_for_TransportStream_Global_Service = Factory::create();
            // $eventLoop_for_TransportStream_Global_Service->addPeriodicTimer(5, function () {
            // $tsDuckData = shell_exec("tsp -I http {$streamUrl} -P until -s 1 -P analyze --normalized -O drop");
            // });

            // spuštění tsducku pro diagnostiku kanálu
            $tsDuckData = shell_exec("timeout --foreground 2s tsp -I http {$streamUrl} -P until -s 1 -P analyze --normalized -O drop");

            // ověření zda analýza selhala či nikoliv
            if (empty($tsDuckData)) {

                // analýza selhala, kanál nejspíše není funkční
                // kanál nyní označíme jako nefunkční, aktualizujeme status kanálu a následně uložíme do historie, pro budoucí výpis
                // před aktualizací statusu, oveření zda kanál již posledním uloženým statusem není označen jako nefunkční

                // dispatch FFprobe , pokud i ta selze => kanál nefunguje od verze 0.3
                // dispatch(new FFprobeDiagnostic($streamUrl, $streamId, null)); // QUEUE
                FFprobeController::ffprobe_diagnostic($streamUrl, $streamId, null);
            }

            // stream funguje
            // vyčtou se ze streamu všechny možná data, která případně pomohou diagnostikovat chyby ve streamu
            // převod stringu do pole
            else {
                // fn pro převedení stringu do pole
                $tsduckArr = self::convert_tsduck_string_to_array($tsDuckData);

                // kanál není ve statusu error, dojde k vyhledání, zda existuje v tabulce channels_which_waiting_for_notifications a odebrání
                if (ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->first()) {
                    // odebrání záznamu z tabulky
                    ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->delete();
                }

                /**
                 * ---------------------------------------------------------------------------------------------------------------------------------------
                 * EVENT LOOP
                 * vše co je v eventLoop_for_TS_Global_Service , funguje async => rychlejší odbavení / zpracování dat bez nutnosti cekání, I/O no blocking
                 * ---------------------------------------------------------------------------------------------------------------------------------------
                 */

                // $eventLoop = Factory::create();


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

                if (array_key_exists('service', $tsduckArr)) {
                    // self::collect_service_from_tsduckArr($tsduckArr["service"], $streamId);
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


                // $eventLoop->run(); // konec event loopu
            }

            // analyzování výstupu z jednotlivých podruzných funkcí
            if ($ts_data["status"] == "issue" || $global_data["status"] == "issue" || $pids_data["status"] == "issue") {
                // update záznamu na issue
                if ($streamInfoData->status != "issue") {
                    Stream::where('id', $streamId)->update(["status" => "issue"]);
                }

                // zpracování alertů
                // vyhledání zda existuje v poly klic "dataAlert"
                if (array_key_exists("dataAlert", $ts_data)) {
                    foreach ($ts_data["dataAlert"] as $tsDataAlert) {
                        // uložení pokud již neexistují

                        if (!StreamAlert::where('stream_id', $streamId)->where('status', $tsDataAlert['status'])->first()) {
                            // ulození alertu
                            StreamAlert::create([
                                'stream_id' => $streamId,
                                'status' => $tsDataAlert['status'],
                                'message' => $tsDataAlert['message']
                            ]);

                            // uložení záznamu do historie, pokud poslední zaznam není $streamAlertGlobal['status']
                            if (StreamHistory::where('stream_id', $streamId)->first()) {
                                if (StreamHistory::where('stream_id', $streamId)->orderBy('id', 'asc')->first()->status != $tsDataAlert['status']) {
                                    StreamHistory::create([
                                        'stream_id' => $streamId,
                                        'status' => $tsDataAlert['status']
                                    ]);
                                }
                            } else {

                                StreamHistory::create([
                                    'stream_id' => $streamId,
                                    'status' => $tsDataAlert['status']
                                ]);
                            }
                        }
                    }
                }

                if (array_key_exists("dataAlert", $pids_data)) {
                    foreach ($pids_data["dataAlert"] as $streamDataAlert) {
                        // uložení pokud již neexistují

                        if (!StreamAlert::where('stream_id', $streamId)->where('status', $streamDataAlert['status'])->first()) {
                            // ulození alertu
                            StreamAlert::create([
                                'stream_id' => $streamId,
                                'status' => $streamDataAlert['status'],
                                'message' => $streamDataAlert['message']
                            ]);

                            // uložení záznamu do historie, pokud poslední zaznam není $streamAlertGlobal['status']
                            if (StreamHistory::where('stream_id', $streamId)->first()) {
                                if (StreamHistory::where('stream_id', $streamId)->orderBy('id', 'asc')->first()->status != $streamDataAlert['status']) {
                                    StreamHistory::create([
                                        'stream_id' => $streamId,
                                        'status' => $streamDataAlert['status']
                                    ]);
                                }
                            } else {

                                StreamHistory::create([
                                    'stream_id' => $streamId,
                                    'status' => $streamDataAlert['status']
                                ]);
                            }
                        }
                    }
                }

                if (array_key_exists("dataAlert", $global_data)) {
                    foreach ($global_data["dataAlert"] as $streamAlertGlobal) {
                        // uložení pokud již neexistují

                        if (!StreamAlert::where('stream_id', $streamId)->where('status', $streamAlertGlobal['status'])->first()) {
                            // ulození alertu
                            StreamAlert::create([
                                'stream_id' => $streamId,
                                'status' => $streamAlertGlobal['status'],
                                'message' => $streamAlertGlobal['message']
                            ]);

                            // uložení záznamu do historie, pokud poslední zaznam není $streamAlertGlobal['status']
                            if (StreamHistory::where('stream_id', $streamId)->first()) {
                                if (StreamHistory::where('stream_id', $streamId)->orderBy('id', 'asc')->first()->status != $streamAlertGlobal['status']) {
                                    StreamHistory::create([
                                        'stream_id' => $streamId,
                                        'status' => $streamAlertGlobal['status']
                                    ]);
                                }
                            } else {

                                StreamHistory::create([
                                    'stream_id' => $streamId,
                                    'status' => $streamAlertGlobal['status']
                                ]);
                            }
                        }
                    }
                }
            } else {

                // stream má status success
                if ($streamInfoData->status != "success") {
                    Stream::where('id', $streamId)->update(["status" => "success"]);
                    // odebrání alertů
                    if (StreamAlert::where('stream_id',  $streamId)->first()) {
                        foreach (StreamAlert::where('stream_id',  $streamId)->get() as $alertToDelete) {
                            StreamAlert::where('id', $alertToDelete["id"])->delete();
                        }
                    }
                }
            }

            // kanál není ve statusu error, dojde k vyhledání, zda existuje v tabulce channels_which_waiting_for_notifications a odebrání
            if (ChannelsWhichWaitingForNotification::where('stream_id', $streamId)->first()) {
                // odeslání mail notifikace pokud je zapotřebí
                dispatch(new SendSuccessEmail($streamId));
            }
            // event do mozaiky
            // event(new StreamNotification());
            event(new StreamInfoHistory($streamId));

            sleep(2);
        } // end of loop
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
            $videoBitrate = (int)$pids['video']['bitrate'];
            $videoPid = $pids['video']['pid'];
            $discontinuities = $pids['video']['discontinuities'];
            $scrambled = $pids['video']['scrambled'];

            event(new StreamInfoTsVideoBitrate($streamId, $pids['video']['bitrate'], $videoPid, $discontinuities, $scrambled));
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
            $audioBitrate = (int)$pids['audio']['bitrate'];
            $audioPid = $pids['audio']['pid'];
            $audioDiscontinuities = $pids['audio']['discontinuities'];
            $audioScrambled = $pids['audio']['scrambled'];
            $audioLanguage = $pids['audio']['language'] ?? "language_not_detected";
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

            // kontrola zda existuje záznam v tabulce stream_videos
            // pokud nebude existovat záznam => založí se nový

            if ($streamVideoData = StreamVideo::where('stream_id', $streamId)->first()) {
                // záznam existuje, proběhne kontrola jednotlivých dat, kdy se provede update jen rozdílů
                // streamVideoData obsahuje pole

                if ($streamVideoData['pid'] != (int) $videoPid) {
                    StreamVideo::where('stream_id', $streamId)->update(['pid' => $videoPid]);
                }

                if ($streamVideoData['access'] != $videoAccess) {
                    StreamVideo::where('stream_id', $streamId)->update(['access' => $videoAccess]);
                }

                if ($streamVideoData['discontinuities'] != $discontinuities) {
                    StreamVideo::where('stream_id', $streamId)->update(['discontinuities' => $discontinuities]);
                }

                if ($streamVideoData['scrambled'] != $scrambled) {
                    StreamVideo::where('stream_id', $streamId)->update(['scrambled' => $scrambled]);
                }

                StreamVideo::where('stream_id', $streamId)->update(['bitrate' => $videoBitrate]);
            } else {

                // uložení záznamu bez
                StreamVideo::create([
                    'stream_id' => $streamId,
                    'pid' => (int) $videoPid,
                    'access' => $videoAccess,
                    'discontinuities' => $discontinuities,
                    'scrambled' => $scrambled,
                    'bitrate' => $videoBitrate
                ]);
            }
        }

        /**
         * ---------------------------------------------------------------------------
         * ověření, že je co ukládat do tabulky StreamAudio
         * ---------------------------------------------------------------------------
         */

        if (isset($audioBitrate) && isset($audioPid) && isset($audioDiscontinuities) && isset($audioScrambled) && isset($audioLanguage) && isset($audioAccess)) {

            // kontrola zda existuje záznam v tabulce stream_audios
            // pokud nebude existovat záznam => založí se nový

            event(new StreamInfoAudioBitrate($streamId, $pids['audio']['bitrate'], $audioPid, $audioDiscontinuities, $audioScrambled, $audioLanguage, $audioAccess));

            if ($streamAudioData = StreamAudio::where('stream_id', $streamId)->first()) {
                // záznam existuje, proběhne kontrola jednotlivých dat, kdy se provede update jen rozdílů
                // streamVideoData obsahuje pole
                if ($streamAudioData['pid'] != (int) $audioPid) {
                    StreamAudio::where('stream_id', $streamId)->update(['pid' => $audioPid]);
                }

                if ($streamAudioData['access'] != $audioAccess) {
                    StreamAudio::where('stream_id', $streamId)->update(['access' => $audioAccess]);
                }

                if ($streamAudioData['discontinuities'] != $audioDiscontinuities) {
                    StreamAudio::where('stream_id', $streamId)->update(['discontinuities' => $audioDiscontinuities]);
                }

                if ($streamAudioData['scrambled'] != $audioScrambled) {
                    StreamAudio::where('stream_id', $streamId)->update(['scrambled' => $audioScrambled]);
                }

                //  pokud se změní jazyková stopa ==> ALERT jelikož se mění výstup i uživatelům do TV
                if ($streamAudioData['language'] != $audioLanguage) {
                    StreamAudio::where('stream_id', $streamId)->update(['language' => $audioLanguage]);

                    // vytvoření alertu do Tabulky StreamAlerts
                    // vyhledání, zda již existuje záznam
                    if (!StreamAlert::where('stream_id', $streamId)->where('status', "audio_warning")->first()) {
                        // záznam neexistuje
                        StreamAlert::create([
                            'stream_id' => $streamId,
                            'status' => "audio_warning",
                            'message' => "Změněna jazyková stopa!"
                        ]);
                    }
                }

                StreamAudio::where('stream_id', $streamId)->update(['bitrate' => $audioBitrate]);
            } else {

                // uložení záznamu bez bitrate
                StreamAudio::create([
                    'stream_id' => $streamId,
                    'pid' => (int) $audioPid,
                    'access' => $audioAccess,
                    'discontinuities' => $audioDiscontinuities,
                    'scrambled' => $audioScrambled,
                    'language' => $audioLanguage,
                    'bitrate' => $audioBitrate
                ]);
            }
        }



        /**
         * ---------------------------------------------------------------------------
         * ověření, že je co ukládat do tabulky StreamCa
         * ---------------------------------------------------------------------------
         */

        if (isset($caScrambled) && isset($caAccess)) {

            // kontrola zda existuje záznam v tabulce stream_cas
            // pokud nebude existovat záznam => založí se nový

            if ($streamCaData = StreamCa::where('stream_id', $streamId)->first()) {
                // záznam existuje, proběhne kontrola jednotlivých dat, kdy se provede update jen rozdílů
                if ($streamCaData['access'] != $caAccess) {
                    // update access
                    StreamCa::where('stream_id', $streamId)->update(['access' => $caAccess]);
                }
                if ($streamCaData['description'] != $caDescription) {
                    // update description
                    StreamCa::where('stream_id', $streamId)->update(['description' => $caDescription ?? null]);
                }

                if ($streamCaData['scrambled'] != $caScrambled) {
                    // update scrambled
                    StreamCa::where('stream_id', $streamId)->update(['scrambled' => $caScrambled]);
                }
            } else {

                // uložení záznamu bez bitrate
                StreamCa::create([
                    'stream_id' => $streamId,
                    'description' => $caDescription ?? null,
                    'access' => $caAccess,
                    'scrambled' => $caScrambled
                ]);
            }
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
        // dd($streamData->dohledVidea);

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
    public static function collect_transportStream_from_tsduckArr(array $tsduckArr, string $streamId)
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

        // pokud je hodnota jiná u invalidsyncs , scrambledpids , transporterrors jiná nez 0
        if ($invalidsyncs == "0" && $scrambledpids == "0" && $transporterrors == "0") {

            // overení zda existuje záznam v tabulce StreamAlert , pokud existuje, budou veskere záznamy s daným streamId odebrány
            if (StreamAlert::where('stream_id', $streamId)->first()) {
                foreach (StreamAlert::where('stream_id', $streamId)->get() as $streamAlert) {
                    // odebrání záznamů z tabulky
                    StreamAlert::where('id', $streamAlert['id'])->delete();
                }
            }

            return [
                'status' => "success"
            ];
        } else {

            //  update statusu na issue

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

            if ($scrambledpids != "0") {

                $outputErrStats[] = array(
                    'stream_id' => $streamId,
                    'status' => "scrambledPids_warning",
                    'message' => "Problémy s Pidy"
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
     * funkce na overení zda funguje audio
     *
     * @param array $tsduckArr
     * @param string $streamId
     * @return void
     */
    public static function collect_global_from_tsduckArr(array $tsduckArr, string $streamId)
    {

        $globalPid = null;

        // vyhledání dat
        foreach ($tsduckArr as $global) {

            if (Str::contains($global, 'pid=')) {
                // zpracování
                $globalPid = str_replace('pid=', "", $global);
            }
        }

        if (!is_null($globalPid)) {

            // oveření zda pid je nebo není roven 0
            if ($globalPid == "0") {
                // stream je nejspíše bez audia
                return [
                    'status' => "issue",
                    'dataAlert' => array([
                        'stream_id' => $streamId,
                        'status' => "no_audio",
                        'message' => "Nepodařilo se detekovat audio"
                    ])
                ];
            } else {

                return [
                    'status' => "success"
                ];
            }
        }
    }

    /**
     * funkce pro získání service dat tsid , access=clear , pmtpid , pcrpid , provider , name
     *
     * @param array $tsduckArr
     * @param string $streamId
     * @return void
     */
    public static function collect_service_from_tsduckArr(array $tsduckArr, string $streamId)
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

        // overení zda existuje v Stream_services
        // pokud neexistuje, zalozí se data
        if ($streamServices = StreamService::where('stream_id', $streamId)->first()) {
            // zaznam existuje, pokud postupně zkontrolován
            // pokud je změna, vyvolá se warning

            if ($streamServices->tsid != $tsid) {
                // update
                StreamService::where('stream_id', $streamId)->update(['tsid' => $tsid]);
                // warning
                // overeni zda existuje stream s tsid_warning
                if (!StreamAlert::where('stream_id', $streamId)->where('status', "tsid_warning")->first()) {
                    // create
                    StreamAlert::create([
                        'stream_id' => $streamId,
                        'status' => "tsid_warning",
                        'message' => "Změnilo se Transport Stream id!!"
                    ]);

                    StreamHistory::create([
                        'stream_id' => $streamId,
                        'status' => "tsid_warning"
                    ]);
                }
            }

            if ($streamServices->pmtpid != $pmtpid) {
                // update
                StreamService::where('stream_id', $streamId)->update(['pmtpid' => $pmtpid]);
                // warning
                // overeni zda existuje stream s pmtpid_warning

                if (!StreamAlert::where('stream_id', $streamId)->where('status', "pmtpid_warning")->first()) {
                    // create
                    StreamAlert::create([
                        'stream_id' => $streamId,
                        'status' => "pmtpid_warning",
                        'message' => "Změnil se pmt pid!!"
                    ]);

                    StreamHistory::create([
                        'stream_id' => $streamId,
                        'status' => "pmtpid_warning"
                    ]);
                }
            }

            if ($streamServices->pcrpid != $pcrpid) {
                // update
                StreamService::where('stream_id', $streamId)->update(['pcrpid' => $pcrpid]);
                // warrning
                // overeni zda existuje stream s pcrpid_warning

                if (!StreamAlert::where('stream_id', $streamId)->where('status', "pcrpid_warning")->first()) {
                    // create
                    StreamAlert::create([
                        'stream_id' => $streamId,
                        'status' => "pcrpid_warning",
                        'message' => "Změnil se pcr pid!!"
                    ]);

                    StreamHistory::create([
                        'stream_id' => $streamId,
                        'status' => "pcrpid_warning"
                    ]);
                }
            }
        } else {
            // záznam nebyl nalezen, založí se ...
            StreamService::create([
                'stream_id' => $streamId,
                'tsid' => $tsid,
                'pmtpid' => $pmtpid,
                'pcrpid' => $pcrpid,
            ]);
        }
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
     * @return void
     */
    public static function collect_pids_from_tsduckArr(array $tsduckArr)
    {
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
            }
            $videoPidOutputData = [
                'pid' => $videoPidStr,
                'discontinuities' => $videoDiscontinuities,
                'description' => $videoDescription,
                'bitrate' => $videoBitrate,
                'scrambled' => $videoScrambled,
                'access' => $videoAccess
            ];
        } else {

            $videoPidOutputData = $videoPid;
        }


        // zpracování audio pidu

        // pokud neexistuje $audioPid ==> vytvoření $audioPid = null
        $audioPid = $audioPid ?? null;
        if (!is_null($audioPid)) {
            foreach ($audioPid as $audioPidDataArr) {

                // pid , access , language , bitrate , scrambled , discontinuities , description
                foreach ($audioPidDataArr as $audioPidData) {

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
}
