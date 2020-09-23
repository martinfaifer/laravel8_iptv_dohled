<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // some code here ...aphp artisan make:test UserTest
    }
}
