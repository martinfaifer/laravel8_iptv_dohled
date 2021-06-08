<?php

namespace App\Http\Controllers;

use App\Models\SystemProccess;
use Illuminate\Http\Request;

class SystemProccessController extends Controller
{

    /**
     * funkce, která spustí self diagnostiku a navrátí Pid pro uložení do db
     *
     * @return integer
     */
    public static function start_self_check_and_return_pid(): int
    {

        $selfCheckPid = shell_exec("nohup php artisan command:selfCheck > /dev/null 2>&1 & echo $!; ");

        return intval($selfCheckPid);
    }

    /**
     * funkce na kontrolu selfcheck funkce
     *
     * @return void
     */
    public static function check_if_self_check_running(): void
    {
        // pokud existuje process_name redis_server , kontrola existence pidu
        // pokud neexistuje start redis_serveru a ulozeni pidu do db
        if (!SystemProccess::where('process_name', "self_check")->first()) {
            // process neexistuje
            $selfCheckPid = self::start_self_check_and_return_pid();

            // ulození pidu do db
            SystemProccess::create([
                'process_name' => "self_check",
                'pid' => intval($selfCheckPid)
            ]);
            return;
        } else {

            // záznam existuje, overuje se, zda pid existuje

            if (SystemController::check_if_process_running(SystemProccess::where('process_name', "self_check")->first()->pid) == "running") {
                // redis server vypadá, že je v pořádku
            } else {

                // pid se nepodařilo najít, dojde ke spuštění a následnéhu updatu pidu v db
                $selfCheckPid = self::start_self_check_and_return_pid();

                // update záznamu
                SystemProccess::where('process_name', "self_check")->update(['pid' => intval($selfCheckPid)]);

                return;
            }
        }
    }

    /**
     * funkce pro start redis serveru a navrácení pidu
     *
     * @return integer
     */
    public static function start_redis_server_return_pid(): int
    {
        $redisPid = shell_exec("redis-server --daemonize yes > /dev/null 2>&1 & echo $!; ");
        return intval($redisPid);
    }

    /**
     * Funkce na kontrolu funkčnosti redis serveru a případné spuštění
     *
     * @return void
     */
    public static function check_if_running_redis_server(): void
    {

        // pokud existuje process_name redis_server , kontrola existence pidu
        // pokud neexistuje start redis_serveru a ulozeni pidu do db
        if (!SystemProccess::where('process_name', "redis_server")->first()) {
            // process neexistuje
            $redisPid = self::start_redis_server_return_pid();

            // ulození pidu do db
            SystemProccess::create([
                'process_name' => "redis_server",
                'pid' => intval($redisPid)
            ]);
        } else {

            // záznam existuje, overuje se, zda pid existuje

            if (SystemController::check_if_process_running(SystemProccess::where('process_name', "redis_server")->first()->pid) == "running") {
                // redis server vypadá, že je v pořádku
            } else {

                // pid se nepodařilo najít, dojde ke spuštění a následnéhu updatu pidu v db
                $redisPid = self::start_redis_server_return_pid();

                // update záznamu
                SystemProccess::where('process_name', "redis_server")->update(['pid' => intval($redisPid)]);
            }
        }
    }

    /**
     * funkce, která hlídá zda existuje queue worker
     *
     * pokud eistuje, overuje se pid => pokud pid je OK vse je OK , pokud pid neexistuje, spustí se nový worker a aktualizuje se záznam v db
     *
     * process_name = queue_worker
     *
     * @return void
     */
    public static function check_if_running_queue_worker_and_if_not_start_and_return_pid(): void
    {

        // pokud neexistuje v tabulce queue worker , vyvolání akce na spustení
        if (!SystemProccess::where('process_name', 'like', "%queue_worker%")->first()) {
            for ($x = 0; $x <= 250;) {


                $queuePid = self::start_queue_and_return_pid();

                // uložení pidu do db
                SystemProccess::create([
                    'process_name' => "queue_worker{$x}",
                    'pid' => intval($queuePid)
                ]);
                $x++;
            }
        } else {
            // záznam existuje, overuje se, zda pid existuje

            SystemProccess::where('process_name', 'like', "%queue_worker%")->get()->each(function ($worker) {
                if (SystemController::check_if_process_running($worker['pid']) == "running") {
                    // queue vypadá, že je v pořádku
                } else {
                    // pid se nepodařilo najít, dojde ke spuštění a následnéhu updatu pidu v db
                    $queuePid = self::start_queue_and_return_pid();

                    $pocetProcesu = SystemProccess::where('process_name', 'like', "%queue_worker")->get()->count();
                    $pocetProcesu++;
                    // update záznamu
                    SystemProccess::where('process_name', "queue_worker{$pocetProcesu}")->update(['pid' => intval($queuePid)]);

                    return;
                }
            });
        }
    }


    /**
     * funkce na spustení queue na pozadí a vrácení pidu
     *
     * @return int
     */
    public static function start_queue_and_return_pid()
    {
        $queuePid = shell_exec("nohup php artisan queue:work --sleep=1 --tries=2 --daemon > /dev/null 2>&1 & echo $!; ");

        return intval($queuePid);
    }



    /**
     * funkce, která hlídá zda existuje websocket server
     *
     * pokud eistuje, overuje se pid => pokud pid je OK vse je OK , pokud pid neexistuje, spustí se nový worker a aktualizuje se záznam v db
     *
     * process_name = websocekt_server
     *
     * @return void
     */
    public static function check_if_running_websocekt_server_and_if_not_start_and_return_pid(): void
    {

        // pokud neexistuje v tabulce queue worker , vyvolání akce na spustení
        if (!SystemProccess::where('process_name', "websocekt_server")->first()) {
            $websocektPid = self::start_websocket_server_and_return_pid();

            // uložení pidu do db
            SystemProccess::create([
                'process_name' => "websocekt_server",
                'pid' => intval($websocektPid)
            ]);
            return;
        } else {
            // záznam existuje, overuje se, zda pid existuje

            if (SystemController::check_if_process_running(SystemProccess::where('process_name', "websocekt_server")->first()->pid) == "running") {
                // queue vypadá, že je v pořádku
            } else {

                // pid se nepodařilo najít, dojde ke spuštění a následnéhu updatu pidu v db
                $websocektPid = self::start_websocket_server_and_return_pid();

                // update záznamu
                SystemProccess::where('process_name', "websocekt_server")->update(['pid' => intval($websocektPid)]);
            }
        }
    }

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


    /**
     * fn pro killnutí vsech systémových procesů
     *
     * @return void
     */
    public static function kill_all_processes(): void
    {

        if (SystemProccess::first()) {
            foreach (SystemProccess::all() as $process) {
                StreamController::stop_diagnostic_stream_from_backend($process['pid']);

                SystemProccess::where('id', $process['id'])->delete();
            }
        }
    }
}
