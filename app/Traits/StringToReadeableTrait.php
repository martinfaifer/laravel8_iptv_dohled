<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait StringToReadeableTrait
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
            // title: vynecháváme jelikož pro nás je to zbytečný udaj
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
}
