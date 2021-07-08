<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Diagnostic\StreamDiagnosticController;
use App\Models\SystemProccess;

class SystemProccessController extends Controller
{

    /**
     * funkce na spustení websocket serveru na pozadí a vrácení pidu
     *
     * @return int
     */
    public static function start_websocket_server_and_return_pid()
    {
        $websocketPid = shell_exec("nohup php artisan websockets:serve > /dev/null 2>&1 & echo $!; ");

        return intval($websocketPid);
    }


    /**
     * funkce na realtime overení, ze streamy funguji jak mají
     * pid se uklada do db a každou minutu se kotnroluje zda fugnuje
     *
     * @return void
     */
    public static function check_if_streams_running_correctly(): void
    {
        // pokud neexistuje v tabulce queue worker , vyvolání akce na spustení
        if (!SystemProccess::where('process_name', "stream_check")->first()) {
            $pidStreamCheck = shell_exec('php artisan command:check_if_serverices_running > /dev/null 2>&1 & echo $!; ');

            // uložení pidu do db
            SystemProccess::create([
                'process_name' => "stream_check",
                'pid' => intval($pidStreamCheck)
            ]);
        } else {
            // záznam existuje, overuje se, zda pid existuje

            if (SystemController::check_if_process_running(SystemProccess::where('process_name', "stream_check")->first()->pid) == "running") {
                // queue vypadá, že je v pořádku
            } else {

                // pid se nepodařilo najít, dojde ke spuštění a následnéhu updatu pidu v db
                $pidStreamCheck = shell_exec('php artisan command:check_if_serverices_running > /dev/null 2>&1 & echo $!; ');

                // update záznamu
                SystemProccess::where('process_name', "stream_check")->update(['pid' => intval($pidStreamCheck)]);
            }
        }
    }
}
