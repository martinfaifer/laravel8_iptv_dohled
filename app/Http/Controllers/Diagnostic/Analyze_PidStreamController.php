<?php

namespace App\Http\Controllers\Diagnostic;

use App\Http\Controllers\Controller;
use App\Events\StreamInfoAudioBitrate;
use App\Events\StreamInfoCa;
use App\Events\StreamInfoTsVideoBitrate;
use App\Jobs\StreamNotificationLimit;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use App\Traits\CacheTrait;

class Analyze_PidStreamController extends Controller
{
    use CacheTrait;
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
     * @return array
     */
    public static function collect_pids_from_tsduckArr(array $tsduckArr): array
    {
        $videoDescription = null;
        $videoPid = null;
        $videoBitrate = "";

        foreach ($tsduckArr as $pid) {

            foreach ($pid as $dataInsidePid) {
                // ulození dat do cache pro každý pid co je v loopu
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

        if (!is_null($videoPid)) {

            foreach ($videoPid as $videoPidData) {

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
                'videoDescription' => $videoDescription,
            ];
        } else {
            // video pid neexituje = null;
            $videoPidOutputData = $videoPid;
        }

        // zpracování audio pidů
        // pokud neexistuje $audioPid ==> vytvoření $audioPid = null
        if (!isset($audioPid)) {
            $audioPid = null;
        }

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
        if (!isset($caPid)) {
            $caPid = null;
        }

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
     * @param object $stream
     * @return array
     */
    public static function analyze_pids_and_storeData(array $pids, object $stream): array
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
            }
            // video se transcoduje
            $videoAccess = "success";

            // získání hodnoty bitrate
            // pokud bude video bitrate = 0 nejspíše chyba
            if (!array_key_exists("bitrate", $pids['video'])) {
                $videoBitrate = "1";
            }
            $videoBitrate = $pids['video']['bitrate'];

            if (!array_key_exists("pid", $pids['video'])) {
                $videoPid = "0";
            }
            $videoPid = $pids['video']['pid'];

            if (!array_key_exists("discontinuities", $pids['video'])) {
                $discontinuities = "0";
            }
            $discontinuities = $pids['video']['discontinuities'];

            if (!array_key_exists("scrambled", $pids['video'])) {
                $scrambled = "0";
            }
            $scrambled = $pids['video']['scrambled'];

            if (!array_key_exists("videoDescription", $pids['video'])) {
                $videoDescription = "bez popisu";
            }
            $videoDescription = $pids['video']['videoDescription'];

            // odeslání broadcastu + uložení do cache tadového toku
            event(new StreamInfoTsVideoBitrate($stream->id, $videoBitrate, $videoPid, $discontinuities, $scrambled, $videoAccess, $videoDescription));

            self::store_pid_bitrate_scrambled_and_discontinuity($stream->id . "_video_bitrate_" . date('H:i:s'), $videoBitrate, $scrambled, $discontinuities, $videoPid);
            self::store_scrambled($stream->id . "_video_scrambled", $scrambled);
        }


        // zpracování audia
        // pokud proměnná pids['audio'] není null budeme zpracovávat
        // pokud by byla null tak se můwže jednat o možnou chybu nebo stream je pouze audio, což se týká radio kanálů => tuto informaci musí nést kanál u sebe
        if (!is_null($pids['audio'])) {

            // vyhledání klíče access a vyhodnocení
            if ($pids['audio']['access'] != "clear") {
                // pokud hodnota není rovná "clear" => audio se netranscoduje
                $audioAccess = "error";
            }
            // audio se transcoduje
            $audioAccess = "success";


            // získání hodnoty bitrate
            if (!array_key_exists("bitrate", $pids['audio'])) {
                $audioBitrate = "1";
            }
            $audioBitrate = $pids['audio']['bitrate'];

            if (!array_key_exists("pid", $pids['audio'])) {
                $audioPid = "0";
            }
            $audioPid = $pids['audio']['pid'];

            if (!array_key_exists("discontinuities", $pids['audio'])) {
                $audioDiscontinuities = "0";
            }
            $audioDiscontinuities = $pids['audio']['discontinuities'];

            if (!array_key_exists("scrambled", $pids['audio'])) {
                $audioScrambled = "0";
            }
            $audioScrambled = $pids['audio']['scrambled'];

            if (!array_key_exists("language", $pids['audio'])) {
                $audioLanguage = "language_not_detected";
            } else {
                $audioLanguage = $pids['audio']['language'];
            }

            if (!array_key_exists("description", $pids['audio'])) {
                $audioDescription = "";
            } else {
                $audioDescription = $pids['audio']['description'];
            }

            event(new StreamInfoAudioBitrate($stream->id, $audioBitrate, $audioPid, $audioDiscontinuities, $audioScrambled, $audioLanguage, $audioAccess, $audioDescription));

            self::store_pid_bitrate_scrambled_and_discontinuity($stream->id . "_audio_bitrate_" . date('H:i:s'), $audioBitrate, $audioScrambled, $audioDiscontinuities, $audioPid);
            self::store_scrambled($stream->id . "_audio_scrambled", $audioScrambled);
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

            event(new StreamInfoCa($stream->id, $caDescription ?? null, $caAccess, $caScrambled));
        }

        // Dispatch JOB pouze pokud hodnoty jsou jiné než 0 až na videoBitrate
        if ($videoBitrate != "0" || $discontinuities != "0" || $scrambled != "0" ||  $audioDiscontinuities != "0" || $audioScrambled != "0") {
            // dispatch Job StreamNotificationLimit
            dispatch(new StreamNotificationLimit($stream->id, $videoBitrate, $discontinuities, $scrambled, $audioDiscontinuities, $audioScrambled));
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

        // pokud streamData->dohledVidea && streamData->dohledAudia jsou obojí true , zpracuje se vše jak je vidět zde dole

        return self::check_values_and_return_array($stream, $videoBitrate, $audioBitrate, $audioAccess, $videoAccess);
    }


    protected static function check_values_and_return_array(object $stream, $videoBitrate, $audioBitrate, $audioAccess, $videoAccess): array
    {
        if ($stream->dohledVidea === true && $stream->dohledVolume === true) {

            // kontrola zda existují hodnoty
            if (isset($audioAccess, $videoAccess)) {
                if ($audioAccess === 'success' && $videoAccess === 'success') {

                    // overení, ze kanál má hodnotu success
                    if ($stream->status != 'success') {
                        return [
                            'status' => "success"
                        ];
                    }
                }
                return [
                    'status' => "issue",
                    'alert_status' => "no_dekrypt",
                    'msg' => "Audio nebo video se nedekryptuje!"
                ];
            }
        }

        if (isset($videoBitrate, $audioBitrate)) {
            if ($videoBitrate === "0" || $audioBitrate === "0") {
                // video nebo audio bitrate je = 0
                if ($videoBitrate === "0") {
                    return [
                        'status' => "issue",
                        'alert_status' => "no_video_bitrate",
                        'msg' => "Video má nulový datový tok!"
                    ];
                }
                return [
                    'status' => "issue",
                    'alert_status' => "no_audio_bitrate",
                    'msg' => "Audio má nulový bitrate!"
                ];
            }
            return [
                'status' => "success"
            ];
        }

        // pokud se nedohleduje video ( radio kanál )
        if ($stream->dohledVidea === false && $stream->dohledVolume === true) {
            if (isset($audioAccess)) {
                if ($audioAccess == 'success') {
                    // overení, ze kanál má hodnotu success
                    return [
                        'status' => "success"
                    ];
                }
                return [
                    'status' => "issue",
                    'alert_status' => "no_dekrypt",
                    'msg' => "Audio se nedekryptuje"
                ];
            }
        }

        // dohleduje se video, ale ne audio
        if ($stream->dohledVidea === true && $stream->dohledVolume === false) {
            if (isset($videoAccess)) {
                if ($videoAccess == 'success') {
                    // overení, ze kanál má hodnotu success
                    return [
                        'status' => "success"
                    ];
                }
                return [
                    'status' => "issue",
                    'alert_status' => "no_dekrypt",
                    'msg' => "video se nedekryptuje"
                ];
            }
        }
        return [
            'status' => "success"
        ];
    }
}
