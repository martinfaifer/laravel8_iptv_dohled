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

use App\Models\AudioBitrate;
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

                /**
                 * ---------------------------------------------------------------------------------------------------------------------------------------
                 * EVENT LOOP
                 * vše co je v eventLoop_for_TS_Global_Service , funguje async => rychlejší odbavení / zpracování dat bez nutnosti cekání, I/O noblocking
                 * ---------------------------------------------------------------------------------------------------------------------------------------
                 */

                $eventLoop_for_TransportStream_Global_Service = Factory::create();

                // zpracování pole
                // vyhledání specifických klíčů, dle kterých se pole zpracuje
                if (array_key_exists('ts', $tsduckArr)) {
                    self::collect_transportStream_from_tsduckArr($tsduckArr["ts"], $stream["id"]);
                }

                if (array_key_exists('global', $tsduckArr)) {
                    self::collect_global_from_tsduckArr($tsduckArr["global"], $stream["id"]);
                }

                if (array_key_exists('service', $tsduckArr)) {
                    self::collect_service_from_tsduckArr($tsduckArr["service"], $stream['id']);
                }

                $eventLoop_for_TransportStream_Global_Service->run(); // konec event loopu

                // array || null video, array || null audio, array || null ca
                // tato funkce je nejdulezitejsi z cele diagnostiky
                if (array_key_exists('pids', $tsduckArr)) {
                    $pids = self::collect_pids_from_tsduckArr($tsduckArr["pids"]);
                }


                // Zpracování pidů
                // od teto analýzy se odvíjí téměř veškeré informace o streamu

                self::analyze_pids_and_storeData($pids, $stream["id"]);
            }
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
     * @param array $streamId
     * @return boolean
     */
    public static function analyze_pids_and_storeData(array $pids, array $streamId)
    {

        // zpracování videa
        // kontrola statusu access
        // zaznamenání video datového toku , scrambled, discontinuities
        foreach ($pids['video'] as $videoData) {
            // pokud proměnná videoData není null budeme zpracovávat
            // pokud by byla null tak se můwže jednat o možnou chybu nebo stream je pouze audio, což se týká radio kanálů => tuto informaci musí nést kanál u sebe
            if (!is_null($videoData)) {
                // vyhledání klíče access a vyhodnocení
                if ($videoData['access'] != "clear") {
                    // pokud hodnota není rovná "clear" => video se netranscoduje
                    $videoAccess = "error";
                } else {
                    // video se transcoduje
                    $videoAccess = "success";
                }

                // získání hodnoty bitrate
                $videoBitrate = (int)$videoData['bitrate'] / 1048576; //Mbps
                $videoPid = $videoData['pid'];
                $discontinuities = $videoData['discontinuities'];
                $scrambled = $videoData['scrambled'];
            }
        }


        // zpracování audia
        foreach ($pids['audio'] as $audioData) {
            // pokud proměnná videoData není null budeme zpracovávat
            // pokud by byla null tak se můwže jednat o možnou chybu nebo stream je pouze audio, což se týká radio kanálů => tuto informaci musí nést kanál u sebe
            if (!is_null($audioData)) {
                // vyhledání klíče access a vyhodnocení
                if ($audioData['access'] != "clear") {
                    // pokud hodnota není rovná "clear" => audio se netranscoduje
                    $audioAccess = "error";
                } else {
                    // audio se transcoduje
                    $audioAccess = "success";
                }

                // získání hodnoty bitrate
                $audioBitrate = (int)$audioData['bitrate'] / 1048576; //Mbps
                $audioPid = $audioData['pid'];
                $audioDiscontinuities = $audioData['discontinuities'];
                $audioScrambled = $audioData['scrambled'];
                $audioLanguage = $audioData['language'];
            }
        }


        // zpracování CA
        foreach ($pids['ca'] as $caData) {

            // pokud proměnná videoData není null budeme zpracovávat
            // pokud by byla null tak se můwže jednat o možnou chybu nebo stream je pouze audio, což se týká radio kanálů => tuto informaci musí nést kanál u sebe
            if (!is_null($caData)) {
                // vyhledání klíče access a vyhodnocení
                if ($caData['access'] != "clear") {
                    // pokud hodnota není rovná "clear" => audio se netranscoduje
                    $caAccess = "error";
                } else {
                    // audio se transcoduje
                    $caAccess = "success";
                }

                // získání hodnot
                $caDescription = $caData['description'];
                $caScrambled = $caData['scrambled'];
            }
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
            } else {

                // uložení záznamu bez bitrate
                StreamVideo::create([
                    'stream_id' => $streamId,
                    'pid' => (int) $videoPid,
                    'access' => $videoAccess,
                    'discontinuities' => $discontinuities,
                    'scrambled' => $scrambled
                ]);
            }


            // uložení záznamu videoBitrate do tabulky
            VideoBitrate::create([
                'stream_id' => $streamId,
                'bitrate' => $videoBitrate
            ]);
        }

        /**
         * ---------------------------------------------------------------------------
         * ověření, že je co ukládat do tabulky StreamAudio
         * ---------------------------------------------------------------------------
         */

        if (isset($audioBitrate) && isset($audioPid) && isset($audioDiscontinuities) && isset($audioScrambled) && isset($audioLanguage) && isset($audioAccess)) {

            // kontrola zda existuje záznam v tabulce stream_audios
            // pokud nebude existovat záznam => založí se nový

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
            } else {

                // uložení záznamu bez bitrate
                StreamAudio::create([
                    'stream_id' => $streamId,
                    'pid' => (int) $audioPid,
                    'access' => $audioAccess,
                    'discontinuities' => $audioDiscontinuities,
                    'scrambled' => $audioScrambled,
                    'language' => $audioLanguage
                ]);
            }


            // uložení záznamu audioBitrate do tabulky
            AudioBitrate::create([
                'stream_id' => $streamId,
                'bitrate' => $audioBitrate
            ]);
        }



        /**
         * ---------------------------------------------------------------------------
         * ověření, že je co ukládat do tabulky StreamCa
         * ---------------------------------------------------------------------------
         */

        if (isset($caDescription) && isset($caScrambled) && isset($caAccess)) {

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
                    StreamCa::where('stream_id', $streamId)->update(['description' => $caDescription]);
                }

                if ($streamCaData['scrambled'] != $caScrambled) {
                    // update scrambled
                    StreamCa::where('stream_id', $streamId)->update(['scrambled' => $caScrambled]);
                }
            } else {

                // uložení záznamu bez bitrate
                StreamCa::create([
                    'stream_id' => $streamId,
                    'description' => $caDescription,
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
        $streamData = Stream::find($streamId);

        // pokud streamData->dohledVidea && streamData->dohledAudia jsou obojí true , zpracuje se vše jak je vidět zde dole
        if ($streamData->dohledVidea == true && $streamData->dohledAudia == true) {
            // kontrola zda existují hodnoty
            if (isset($audioAccess) && isset($videoAccess) && $audioAccess == 'clear' && $videoAccess == 'clear') {

                // overení, ze kanál má hodnotu success
                if ($streamData->status != 'success') {
                    Stream::where('id', $streamId)->update(['status' => "success"]);
                }
            } else {
                Stream::where('id', $streamId)->update(['status' => "not_scrambled"]);
            }

            // pokud se nedohleduje video ( radio kanál )
        } else if ($streamData->dohledVidea == false && $streamData->dohledAudia == true) {
            if (isset($audioAccess) && $audioAccess == 'clear') {
                // overení, ze kanál má hodnotu success
                if ($streamData->status != 'success') {
                    Stream::where('id', $streamId)->update(['status' => "success"]);
                }
            }

            // dohleduje se video, ale ne audio
        } else if ($streamData->dohledVidea == true && $streamData->dohledAudia == false) {
            if (isset($videoAccess) && $videoAccess == 'clear') {
                // overení, ze kanál má hodnotu success
                if ($streamData->status != 'success') {
                    Stream::where('id', $streamId)->update(['status' => "success"]);
                }
            }
        }
    }


    /**
     * funkce pro získání invalidsyncs , scrambledpids , transporterrors
     *
     * @param array $tsduckArr
     * @param array $streamId
     * @return array
     */
    public static function collect_transportStream_from_tsduckArr(array $tsduckArr, array $streamId)
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

        // uložení TS dat do stream_t_sdatas
        StreamTSdata::create([
            'stream_id' => $streamId,
            'invalidsyncs' => $invalidsyncs,
            'scrambledpids' => $scrambledpids,
            'transporterrors' => $transporterrors
        ]);
    }

    /**
     * funknce pro získání celkového bitratu "bitrate"
     *
     * @param array $tsduckArr
     * @param array $streamId
     * @return array
     */
    public static function collect_global_from_tsduckArr(array $tsduckArr, array $streamId)
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

        if (!is_null($maxBitrate)) {
            // uložení do StreamBitrate
            StreamBitrate::create([
                'stream_id' => $streamId,
                'bitrate' => $maxBitrate
            ]);
        }
    }

    /**
     * funkce pro získání service dat tsid , access=clear , pmtpid , pcrpid , provider , name
     *
     * @param array $tsduckArr
     * @param array $streamId
     * @return array
     */
    public static function collect_service_from_tsduckArr(array $tsduckArr, array $streamId)
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

            // // nejslozitejsi cast!!!
            // // pokud existuje access=clear => tak se kanál jako takový dekryptuje
            // if (Str::contains($service, 'access=clear')) {
            //     // zpracování
            //     $access = "access";
            // } else {
            //     $access = "scrambled";
            // }
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
                    StreamAlert::cerate([
                        'stream_id' => $streamId,
                        'status' => "tsid_warning",
                        'message' => "Změnilo se Transport Stream id!!"
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
                    StreamAlert::cerate([
                        'stream_id' => $streamId,
                        'status' => "pmtpid_warning",
                        'message' => "Změnil se pmt pid!!"
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
                    StreamAlert::cerate([
                        'stream_id' => $streamId,
                        'status' => "pcrpid_warning",
                        'message' => "Změnil se pcr pid!!"
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
     * @return array ca  array || null
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
