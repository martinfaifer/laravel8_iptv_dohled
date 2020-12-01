<?php

namespace App\Http\Controllers;

use App\Models\CcError;
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
}
