<?php

namespace App\Http\Controllers\Diagnostic;

use App\Http\Controllers\Controller;

use App\Events\StreamInfoService;
use Illuminate\Support\Str;

class Analyze_ServicesOfStreamController extends Controller
{

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
        $pmtpid = null;
        $pcrpid = null;
        $provider = null;
        $name = null;

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
}
