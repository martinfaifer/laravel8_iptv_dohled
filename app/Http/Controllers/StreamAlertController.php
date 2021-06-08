<?php

namespace App\Http\Controllers;

use App\Models\StreamAlert;

use App\Traits\StreamAlertTrait;

class StreamAlertController extends Controller
{

    use StreamAlertTrait;

    public function index(): array
    {
        return $this->alerts();
    }

    /**
     * funkce na vyhledání a navrácení informací o spadlém streamu, dle streamId
     *
     * @param string $streamId
     * @return array
     */
    public static function return_information_about_issued_stream(string $streamId): array
    {
        $sumAlertInformation = array();

        // pro jistotu overení, zda opravdu existuje nejaka hodnota
        if (!StreamAlert::where('stream_id', $streamId)->first()) {
            // neexistuje žádný záznam, vrcí se prázdné pole
            return [];
        }

        foreach (StreamAlert::where('stream_id', $streamId)->get() as $streamAlertInformation) {
            // statusy
            // audio_warning
            // invalidSync_warning
            // scrambledPids_warning
            // transporterrors_warning
            // tsid_warning
            // pmtpid_warning
            // pcrpid_warning


            // může být více problémů, zobrazení vícerozměrného pole
            $sumAlertInformation[] = array(
                'status' => $streamAlertInformation["status"],
                'message' => $streamAlertInformation["message"]
            );
        }

        return $sumAlertInformation;
    }
}
