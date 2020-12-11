<?php

namespace App\Http\Controllers;

use App\Models\CcError;
use App\Models\Stream;
use Carbon\Carbon;
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

                foreach (CcError::where('streamId', $request->streamId)->where('pozition', "audio")->get() as $audio) {
                    // $this->audio[] = array(
                    //     substr($audio->created_at, 0, 19), $audio->ccError
                    // );

                    $this->audio[] = array(intval($audio->ccError));
                    $this->audioTime[] = array(substr($audio->created_at, 10, 19));
                }
            }

            if (CcError::where('streamId', $request->streamId)->where('pozition', "video")->first()) {


                foreach (CcError::where('streamId', $request->streamId)->where('pozition', "video")->get() as $video) {

                    // $this->video[] = array(
                    //     substr($video->created_at, 0, 19), $video->ccError
                    // );
                    $this->video[] = array(intval($video->ccError));
                    $this->videoTime[] = array(substr($video->created_at, 10, 19));
                }
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
            foreach (CcError::where('expirace', $nyni)->orWhere('expirace', null)->get() as $dataToDelete) {
                CcError::where('id', $dataToDelete["id"])->delete();
            }
        }
    }

    /**
     * fn pro zjistení zda stream má cc errory a přídnný count a přípavu pro graf
     * výpis 8 streamů
     * @return array
     */
    public function take_streams_check_if_exist_cc_and_count()
    {
        $streamCount = 0;
        $ountput = null;
        if (Stream::first()) {
            foreach (Stream::get() as $stream) {
                // vyhledání zda existuje cc error v ccError table dle streamId
                if (CcError::where('streamId', $stream->id)->first()) {
                    $chartCCRadio = [];
                    $chartCCRvideo = [];
                    $defaultCCerror = 0;
                    // existuje minimálně jeden záznam

                    foreach (CcError::where('streamId', $stream->id)->get() as $ccErrorRecord) {
                        if ($ccErrorRecord->pozition == "video") {
                            $chartCCRvideo[] = $ccErrorRecord->ccError;
                            // $chartCreated_atVideo[] = substr($ccErrorRecord->created_at, 11, 19);
                        } else {

                            $chartCCRadio[] = $ccErrorRecord->ccError;
                        }
                        $chartCreated_at[] = substr($ccErrorRecord->created_at, 11, 19);
                        // count ccError
                        if (isset($ccErrors)) {
                            $ccErrors = $ccErrors + (int) $ccErrorRecord->ccError;
                        } else {
                            $ccErrors = $defaultCCerror + (int) $ccErrorRecord->ccError;
                        }
                    }
                    // získání záznamů pro vykreslení
                    $ountput[] = array(
                        'stream' => $stream->nazev,
                        'ccerrors' => $ccErrors,

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
                                'data' => $chartCCRadio
                            ),
                            array(
                                'name' => $stream->nazev . " video",
                                'data' => $chartCCRvideo
                            )
                        ]
                    );

                    unset($ccErrors);
                    unset($chartCCR);
                    unset($chartCreated_at);
                    unset($chartCCRadio);
                    unset($chartCCRvideo);

                    $streamCount++;
                    if ($streamCount >= 9) {
                        break;
                    }
                }
            }
            if (is_null($ountput)) {
                return [
                    'status' => "empty"
                ];
            }
            return [
                'status' => "exist",
                'streams' => $ountput
            ];
        } else {
            return [
                'status' => "empty"
            ];
        }
    }
}
