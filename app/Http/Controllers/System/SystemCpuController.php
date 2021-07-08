<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SystemCpuController extends Controller
{
    public function get_cpu_history_data()
    {
        $xaxis = [];
        $stats = $this->get_core_information();
        foreach ($stats as $key => $stat) {
            for ($i = 120; $i > 1; $i--) {
                if (Cache::has('cpu' . $key . now()->subMinutes($i)->format('H:i'))) {

                    $cache = Cache::get('cpu' . $key  . now()->subMinutes($i)->format('H:i'));

                    if (!isset($name)) {
                        $name['cpu' . $key] = 'cpu' . $key;
                    }

                    if (!array_key_exists('cpu' . $key, $name)) {
                        $name['cpu' . $key] = 'cpu' . $key;
                    }

                    if (array_key_exists('cpu' . $key, $name)) {
                        $data['cpu' . $key][] = $cache['value'];
                        $xaxis[] = $cache['created_at'];
                    }
                }
            }

            if (isset($name['cpu' . $key])) {
                $output[] = [
                    'name' => $name['cpu' . $key],
                    'data' => $data['cpu' . $key]
                ];
            }
        }

        if (isset($output)) {
            return [
                'status' => "success",
                'series' => $output,
                'xaxis' => $xaxis
            ];
        }

        return [
            'status' => "empty"
        ];
    }

    public static function store_cpu_usage(): void
    {
        // získání prvního snapshotu 
        $stat1 = self::get_core_information();
        // použití usleep pro rychlejsí odbavení
        usleep(100000);
        // získání druhého snapshotu 
        $stat2 = self::get_core_information();

        $cpus_data = self::get_cpu_percentages($stat1, $stat2);

        foreach ($cpus_data as $cpu => $cpu_value) {

            Cache::put($cpu . date('H:i'), [
                'cpu' => $cpu,
                'value' => 100 - $cpu_value['idle'],
                'created_at' => date('H:i')
            ], now()->addMinutes(120));
        }
    }

    protected static function get_core_information(): array
    {
        $cores = array();

        $cpu_data_file = file('/proc/stat');
        foreach ($cpu_data_file as $line) {
            if (preg_match('/^cpu[0-9]/', $line)) {
                $info = explode(' ', $line);
                $cores[] = array(
                    'user' => $info[1],
                    'nice' => $info[2],
                    'sys' => $info[3],
                    'idle' => $info[4]
                );
            }
        }
        return $cores;
    }

    protected static function get_cpu_percentages(array $stat1, array $stat2): array
    {
        $cpus = array();

        if (count($stat1) !== count($stat2)) {
            return [];
        }

        for ($i = 0, $l = count($stat1); $i < $l; $i++) {
            $dif = array();
            $dif['user'] = $stat2[$i]['user'] - $stat1[$i]['user'];
            $dif['nice'] = $stat2[$i]['nice'] - $stat1[$i]['nice'];
            $dif['sys'] = $stat2[$i]['sys'] - $stat1[$i]['sys'];
            $dif['idle'] = $stat2[$i]['idle'] - $stat1[$i]['idle'];
            $total = array_sum($dif);
            $cpu = array();
            foreach ($dif as $x => $y) $cpu[$x] = round($y / $total * 100, 1);
            $cpus['cpu' . $i] = $cpu;
        }
        return $cpus;
    }
}
