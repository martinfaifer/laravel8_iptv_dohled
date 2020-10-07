<?php

namespace App\Http\Controllers;

use App\Models\SystemProccess;
use Illuminate\Http\Request;

class SystemProccessController extends Controller
{

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
            return;
        } else {

            // záznam existuje, overuje se, zda pid existuje

            if (SystemController::check_if_process_running(SystemProccess::where('process_name', "redis_server")->first()->pid) == "running") {
                // redis server vypadá, že je v pořádku
            } else {

                // pid se nepodařilo najít, dojde ke spuštění a následnéhu updatu pidu v db
                $redisPid = self::start_redis_server_return_pid();

                // update záznamu
                SystemProccess::where('process_name', "redis_server")->update(['pid' => intval($redisPid)]);

                return;
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
        if (!SystemProccess::where('process_name', "queue_worker")->first()) {
            $queuePid = self::start_queue_and_return_pid();

            // uložení pidu do db
            SystemProccess::create([
                'process_name' => "queue_worker",
                'pid' => intval($queuePid)
            ]);
            return;
        } else {
            // záznam existuje, overuje se, zda pid existuje

            if (SystemController::check_if_process_running(SystemProccess::where('process_name', "queue_worker")->first()->pid) == "running") {
                // queue vypadá, že je v pořádku
            } else {

                // pid se nepodařilo najít, dojde ke spuštění a následnéhu updatu pidu v db
                $queuePid = self::start_queue_and_return_pid();

                // update záznamu
                SystemProccess::where('process_name', "queue_worker")->update(['pid' => intval($queuePid)]);

                return;
            }
        }
    }


    /**
     * funkce na spustení queue na pozadí a vrácení pidu
     *
     * @return string
     */
    public static function start_queue_and_return_pid(): string
    {
        $queuePid = shell_exec("nohup php artisan queue:work --sleep=0 --daemon > /dev/null 2>&1 & echo $!; ");

        return intval($queuePid);
    }
}
