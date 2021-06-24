<?php

namespace App\Http\Controllers;

use App\Models\CcError;
use App\Models\Stream;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CcErrorController extends Controller
{

    public $audio = array();
    public $video = array();
    public $videoTime = array();
    public $audioTime = array();


    /**
     * funkce na založení počtu CCErroru ve streamu
     *
     * @param string $streamId
     * @param string $error
     * @param string $pozice
     * @return void
     */
    public static function store_ccError(string $streamId, string $error, string $pozice): void
    {
        date_default_timezone_set('Europe/Prague');
        if ($error != "0") {
            // vytvoření budoucí expirace
            CcError::create(
                [
                    'streamId' => $streamId,
                    'ccError' => $error,
                    'pozition' => $pozice,
                    'expirace' => date("Y-m-d H:i", strtotime('+2 hours'))
                ]

            );
        }
    }

    /**
     * funkce na vrácení chyb, kdy stream vykazoval CC errory
     *
     * @param Request $request->streamId
     * @return array
     */
    public function get_ccErrors_for_current_stream(Request $request): array
    {
        if (CcError::where('streamId', $request->streamId)->first()) {

            // existuje alespon jeden zaznam s chybou u audia nebo videa

            // vyhledání zda záznam je audio nebo video
            if (CcError::where('streamId', $request->streamId)->where('pozition', "audio")->first()) {

                CcError::where('streamId', $request->streamId)->where('pozition', "audio")->get()->each(function ($audio) {

                    $this->audio[] = array(intval($audio->ccError));
                    $this->audioTime[] = array(substr($audio->created_at, 10, 19));
                });
            }

            if (CcError::where('streamId', $request->streamId)->where('pozition', "video")->first()) {

                CcError::where('streamId', $request->streamId)->where('pozition', "video")->get()->each(function ($video) {
                    $this->video[] = array(intval($video->ccError));
                    $this->videoTime[] = array(substr($video->created_at, 10, 19));
                });
            }

            return [
                'status' => "exist",
                'audio' => Arr::collapse($this->audio),
                'audioTime' => $this->audioTime,
                'video' => Arr::collapse($this->video),
                'videoTime' => $this->videoTime
            ];
        } else {
            // nastesti neexistuje zadny zaznam
            return [
                'status' => "empty"
            ];
        }
    }


    /**
     * funkce na postupné odmazávání dat z tabulky cc_errors
     *
     * mažou se záznamy staší jak 2 hodiny
     * @return void
     */
    public static function prum_every_two_hours(): void
    {
        // aktuální cas
        $nyni = date("Y-m-d") . " " . date("H:i");
        if (CcError::where('expirace', $nyni)->orWhere('expirace', null)->first()) {

            CcError::where('expirace', $nyni)->orWhere('expirace', null)->chunk(50, function ($datasToDelete) {
                foreach ($datasToDelete as $dataToDelete) {
                    $dataToDelete->delete();
                }
            });
        }
    }

    /**
     * fn pro zjistení zda stream má cc errory a přídnný count a přípavu pro graf
     * výpis 8 streamů
     * @return array
     */
    public function take_streams_check_if_exist_cc_and_count()
    {
        $name = [];
        $streamCount = 0;
        $output = null;
        $chartCCRaudio = [];
        $chartCCRvideo = [];

        foreach (Stream::all() as $stream) {
            for ($i = 1; $i <= 60; $i++) {
                if (Cache::has($stream->id . "_video_" . now()->subMinutes($i)->format('H:i'))) {
                    $cache_stream_video = Cache::get($stream->id . "_video_"  . now()->subMinutes($i)->format('H:i'));
                    $chartCCRvideo[] = $cache_stream_video['ccError'];
                }

                if (Cache::has($stream->id . "_audio_" . now()->subMinutes($i)->format('H:i'))) {
                    $cache_stream_audio = Cache::get($stream->id . "_audio_"  . now()->subMinutes($i)->format('H:i'));
                    $chartCCRaudio[] = $cache_stream_audio['ccError'];
                }
                if (isset($cache_stream_video)) {
                    $chartCreated_at[] = $cache_stream_video['created_at'];
                }

                if (!isset($name[$stream->nazev])) {
                    $name[$stream->nazev] = $stream->nazev;
                }

                if (array_key_exists($stream->nazev, $name)) {
                }
            }

            if (isset($chartCreated_at)) {
                $output[] = array(
                    'stream' => $stream->nazev,
                    'ccerrors' => 0,

                    'chartOptions' => [
                        'fill' => array(
                            'colors' => ["#F44336", "#E91E63", "#9C27B0"]
                        ),
                        'chart' => array(
                            'id' => $stream->nazev
                        ),
                        'xaxis' => array(
                            'categories' => $chartCreated_at
                        )
                    ],
                    'series' => [
                        array(
                            'name' => $stream->nazev . " audio",
                            'data' => $chartCCRaudio
                        ),
                        array(
                            'name' => $stream->nazev . " video",
                            'data' => $chartCCRvideo
                        )
                    ]
                );
            }
        }
        if (is_null($output)) {
            return [
                'status' => "empty"
            ];
        }
        return [
            'status' => "exist",
            'streams' => $output
        ];
    }
}
