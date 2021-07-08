<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Jobs\SystemMailAlert;
use App\Models\SystemProccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Probe\ProviderFactory;
use React\EventLoop\Factory;
use Illuminate\Support\Str;
use App\Traits\SystemLogTrait;

class SystemController extends Controller
{
    use SystemLogTrait;

    /**
     * testing fn pro získání kompletních informací ze systému
     *
     * fn se nejspíše bude ještě hodne měnit jelikož ne vse se získává jak je zapotřebí
     *
     * @return void
     */
    public function checkSystemUsage()
    {
        $provider = \Probe\ProviderFactory::create();
        return [
            'cpu_model' => $provider->getCpuModel(),
            'cpu_usage' => $provider->getCpuUsage(),
            'cpu_cores' => $provider->getCpuCores(),
            'free_memory' => $provider->getFreeMem(),
            'used_memory' => $provider->getUsedMem(),
            'total_memory' => $provider->getTotalMem(),
            'total_swap' => $provider->getTotalSwap(),
            'free_swap' => $provider->getFreeSwap(),
            'used_swap' => $provider->getUsedSwap(),
            'os_release' => $provider->getOsRelease(),
            'os_type' => $provider->getOsType(),
            'server_ip' => $provider->getServerIP(),
        ];
    }

    /**
     * fn pro kontrolu systému a následné vyvolání alertingu
     *
     * @return void
     */
    public static function selfCheck()
    {
        $eventLoop = Factory::create();

        $eventLoop->addPeriodicTimer(1, function () {
            if (self::ram() > "80") {
                // zasli mail alert job
                dispatch(new SystemMailAlert("ram"));
                // brodcast message

            }
            if (self::swap() > "10") {
                // system začal swapovat, mail alert job
                dispatch(new SystemMailAlert("swap"));
                // brodcast message

            }

            if (self::hdd() > "80") {
                // mail s málo místem na disku job
                dispatch(new SystemMailAlert("hdd"));
                // brodcast message

            }

            if (self::count_expiration_of_ssl_and_if_expiration_is_lower_than_one_day_send_warning_email() == "alert") {
                dispatch(new SystemMailAlert("ssl"));
            }
        });

        $eventLoop->run();
    }


    /**
     * funkce, která hlídá, zda pid, který je uložený v tabulce streams označený jako process_pid nebo ffmpeg_pid existuje
     *
     * pokud pid neexistuje vyvolá se alert / případně pokud bude žádost pokusí se pustit process sám
     *
     * funknce bude volána
     *
     * @param string $pid
     * @return void | string $pid . "_process_not_running"
     */
    public static function check_if_process_running(string $pid)
    {
        if (file_exists("/proc/{$pid}")) {
            // OK, process uložený pod $pid je aktivní
            return "running";
        } else {
            return "not_running";
        }
    }

    public static function ram()
    {
        try {
            $provider = ProviderFactory::create();
            $totalRam = $provider->getTotalMem() / 1073741824;
            $usedRam = $provider->getUsedMem() / 1073741824;

            $result = round(($usedRam * 100) / $totalRam);

            return $result;
        } catch (\Throwable $th) {
            return "0";
        }
    }

    public static function swap()
    {
        try {
            $provider = ProviderFactory::create();
            $totalSwap = $provider->getTotalSwap() / 1073741824;
            $usedSwap = $provider->getUsedSwap() / 1073741824;

            $result = ($usedSwap * 100) / $totalSwap;

            return round($result);
        } catch (\Throwable $th) {
            return "0";
        }
    }

    public static function hdd()
    {
        try {
            $disk = disk_total_space("/");
            $diskGiga = $disk / 1073741824;

            $freeDisk = disk_free_space("/");
            $freeDiskGiga = $freeDisk / 1073741824;

            $onePercent = $diskGiga / 100;
            $percentsFree = $freeDiskGiga / $onePercent;

            return round($percentsFree);
        } catch (\Throwable $th) {
            return "0";
        }
    }

    public static function get_uptime()
    {
        $uptimeData = shell_exec('uptime -p');
        $uptimeData = str_replace("up", "", $uptimeData);
        $uptimeData = str_replace("days", "d", $uptimeData);
        $uptimeData = str_replace("day", "d", $uptimeData);
        $uptimeData = str_replace("hours", "h", $uptimeData);
        $uptimeData = str_replace("minutes", "min", $uptimeData);
        return $uptimeData;
    }

    /**
     * funkce odelse data o tom jak je server nastavený
     *
     * cpu model, celkem ram, celkem swap, uptime , IP, OS, web server (nginx / apache)
     *
     *
     *
     * @return void
     */
    public static function server_status()
    {

        $provider = ProviderFactory::create();
        $totalMemory = str_replace("\n", "", $provider->getTotalMem());
        $totalMemory = round($totalMemory / 1073741824);

        $totalSwap = str_replace("\n", "", $provider->getTotalSwap());
        $totalSwap = round($totalSwap / 1073741824);

        $nginx = str_replace("\n", "", $provider->isNginx());

        if ($nginx == "") {
            $nginx = "false";
        } else {
            $nginx = "true";
        }

        $apache = str_replace("\n", "", $provider->isApache());
        if ($apache == "") {
            $apache = "false";
        } else {
            $apache = "true";
        }

        return [
            array(
                'popis' => "Uptime",
                'data' => self::get_uptime()
            ),
            array(
                'popis' => "OS",
                'data' => str_replace("\n", "", $provider->getOsRelease())
            ),
            array(
                'popis' => "CPU model",
                'data' => str_replace("\n", "", $provider->getCpuModel())
            ),
            array(
                'popis' => "RAM",
                'data' => $totalMemory . "GB"
            ),
            array(
                'popis' => "SWAP",
                'data' => $totalSwap . "GB"
            ),
            array(
                'popis' => "Server IP",
                'data' => str_replace("\n", "", $provider->getServerIP())
            ),
            array(
                'popis' => "Apache",
                'data' => $apache
            ),
            array(
                'popis' => "Nginx",
                'data' => $nginx
            ),
            array(
                'popis' => "Expirace SSL certifikátu",
                'data' => self::check_web_certificate()
            )

        ];
    }

    /**
     * funkce na ověření platnosti certifikatu
     *
     * @return string
     */
    public static function check_web_certificate()
    {
        $url = env("APP_URL");
        $orignal_parse = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client(
            "ssl://" . $orignal_parse . ":443",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $get
        );
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        return substr(date(DATE_ATOM, $certinfo["validTo_time_t"]), 0, 10); // expirace certifikatu
    }


    /**
     * fn pro výpočet kolik zbívá dní do expirace certifikátu
     *
     * @return string
     */
    public static function count_expiration_of_ssl_and_if_expiration_is_lower_than_one_day_send_warning_email(): string
    {
        $expiraceDatum = self::check_web_certificate();
        $oneDayLeft = strtotime('-1 day');
        $expiraceDatumToLinuxTime = strtotime($expiraceDatum);
        if ($oneDayLeft > $expiraceDatumToLinuxTime) {
            return "alert";
        } else {
            "ok";
        }
    }

    /**
     * funkce na odebrní náhledu, které jsou starsi nez jedna hodina
     *
     * @return void
     */
    public static function oldImgOlderThanOneHour(): void
    {
        $unixTimeMinusHodina = time() - 3600;  // získání unixtimu, který je starší jak jedna hodina
        foreach (scandir((public_path('/storage/channelsImages/'))) as $img) {
            try {
                if (!Str::contains($img, '.')) {
                    self::unlink_image($unixTimeMinusHodina, $img);
                }
            } catch (\Throwable $th) {
                self::create("Delete_images_older_than_One_hour", $th);
            }
        }
    }

    protected static function unlink_image(int $unixTimeMinusHodina, string $img): void
    {
        if ($unixTimeMinusHodina > filemtime(public_path('/storage/channelsImages/' . $img))) {
            unlink(public_path('/storage/channelsImages/' . $img));
        }
    }

    protected function getNetwork(): array
    {
        return [];
    }

    /**
     * fn pro sběr dat ze serveru a ukládání pro budoucí vykreslení do grafů
     *
     * @return void
     */
    public static function get_periodicly_systemLoad_ram_hdd_swap(): void
    {
        $provider = ProviderFactory::create();
        // ram blok
        $usedRam = $provider->getUsedMem() / 1073741824;
        Cache::put('ram_' . date('H:i'), [
            'value' => $usedRam,
            'created_at' => date('H:i')
        ], now()->addMinutes(60));

        // swap
        $usedSwap = $provider->getUsedSwap() / 1073741824;
        Cache::put('swap_' . date('H:i'), [
            'value' => $usedSwap,
            'created_at' => date('H:i')
        ], now()->addMinutes(60));

        // hdd
        $freeDisk = disk_free_space("/");
        $freeDiskGiga = $freeDisk / 1073741824;
        Cache::put('hdd_' . date('H:i'), [
            'value' => $freeDiskGiga,
            'created_at' => date('H:i')
        ], now()->addMinutes(60));

        // load
        $load = sys_getloadavg();
        $loadfiveminutes = round($load[0], 2);
        Cache::put('load_' . date('H:i'), [
            'value' => $loadfiveminutes,
            'created_at' => date('H:i')
        ], now()->addMinutes(60));
    }


    /**
     * fn pro zobrazení historie zatížení systému
     *
     * @return array
     */
    public function load_history_system_usage(): array
    {
        for ($i = 60; $i > 1; $i--) {
            if (Cache::has('load_' . now()->subMinutes($i)->format('H:i'))) {
                $cache = Cache::get('load_' . now()->subMinutes($i)->format('H:i'));

                $seriesData[] = $cache['value'];
                $xaxis[] = $cache['created_at'];
            }
        }

        if (isset($seriesData)) {
            return [
                'status' => "exist",
                'xaxis' => $xaxis,
                'seriesData' => $seriesData
            ];
        }

        return [
            'status' => "empty"
        ];
    }

    /**
     * fn pro vykrelsení area chartu, vyuzití ram
     *
     * @return array
     */
    public function ram_history_system_usage(): array
    {
        for ($i = 60; $i > 1; $i--) {
            if (Cache::has('ram_' . now()->subMinutes($i)->format('H:i'))) {
                $cache = Cache::get('ram_' . now()->subMinutes($i)->format('H:i'));

                $seriesData[] = $cache['value'];
                $xaxis[] = $cache['created_at'];
            }
        }

        if (isset($seriesData)) {
            return [
                'status' => "exist",
                'xaxis' => $xaxis,
                'seriesData' => $seriesData
            ];
        }

        return [
            'status' => "empty"
        ];
    }


    /**
     * fn pro vykrelsení area chartu, vyuzití hdd
     *
     * @return array
     */
    public function hdd_history_system_usage(): array
    {
        for ($i = 60; $i > 1; $i--) {
            if (Cache::has('hdd_' . now()->subMinutes($i)->format('H:i'))) {
                $cache = Cache::get('hdd_' . now()->subMinutes($i)->format('H:i'));

                $seriesData[] = $cache['value'];
                $xaxis[] = $cache['created_at'];
            }
        }

        if (isset($seriesData)) {
            return [
                'status' => "exist",
                'xaxis' => $xaxis,
                'seriesData' => $seriesData
            ];
        }

        return [
            'status' => "empty"
        ];
    }



    /**
     * fn pro vykrelsení area chartu, vyuzití swap
     *
     * @return array
     */
    public function swap_history_system_usage(): array
    {
        for ($i = 60; $i > 1; $i--) {
            if (Cache::has('swap_' . now()->subMinutes($i)->format('H:i'))) {
                $cache = Cache::get('swap_' . now()->subMinutes($i)->format('H:i'));

                $seriesData[] = $cache['value'];
                $xaxis[] = $cache['created_at'];
            }
        }

        if (isset($seriesData)) {
            return [
                'status' => "exist",
                'xaxis' => $xaxis,
                'seriesData' => $seriesData
            ];
        }

        return [
            'status' => "empty"
        ];
    }
}
