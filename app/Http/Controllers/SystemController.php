<?php

namespace App\Http\Controllers;

use App\Events\StreamNotification;
use App\Jobs\SystemMailAlert;
use Illuminate\Http\Request;
use Probe\ProviderFactory;
use React\EventLoop\Factory;

class SystemController extends Controller
{
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


    /**
     * kontrola CPU serveru, výpis vytížení
     *
     * @return void
     */
    public static function cpu()
    {
        $load = sys_getloadavg();
        return round($load[0], 2);
    }


    public static function ram()
    {
        $provider = ProviderFactory::create();

        $provider = ProviderFactory::create();
        $totalRam = $provider->getTotalMem() / 1073741824;
        $usedRam = $provider->getUsedMem() / 1073741824;

        return $result = round(($usedRam * 100) / $totalRam);
    }

    public static function swap()
    {

        $provider = ProviderFactory::create();
        $totalSwap = $provider->getTotalSwap() / 1073741824;
        $usedSwap = $provider->getUsedSwap() / 1073741824;

        return $result = ($usedSwap * 100) / $totalSwap;
    }

    public static function hdd()
    {
        $disk = disk_total_space("/");
        $diskGiga = $disk / 1073741824;

        $freeDisk = disk_free_space("/");
        $freeDiskGiga = $freeDisk / 1073741824;


        // percents %
        $onePercent = $diskGiga / 100;
        $percentsFree = $freeDiskGiga / $onePercent;

        return round($percentsFree);
    }

    public static function get_uptime()
    {
        $uptimeData = shell_exec('uptime -p');
        $uptimeData = str_replace("up", "", $uptimeData);
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
            )

        ];
    }
}
