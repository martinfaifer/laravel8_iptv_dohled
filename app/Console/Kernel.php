<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // killnutí streamu, který existuje v tabulce stoppedStreams
        $schedule->command('command:killStopedStreams')->everyMinute()->runInBackground();
        // stream sheduler
        $schedule->command('command:streamScheduler')->everyMinute()->runInBackground();
        // cistení slozky s obrázky
        $schedule->command('command:deleteImagesOlderTharOneHour')->hourly()->runInBackground();
        // cistení záznamu z tabulky cc_errors
        $schedule->command('stream:prum_CC_errors')->everyMinute()->runInBackground();
        // postupné mazání záznamů z hostorie, které jsou starší než 12h
        $schedule->command('streamHistory:delete_older_then_twelve_hours')->everyMinute()->runInBackground();

        // odmazávání logů z firewallu, které jsou staší než 24h
        $schedule->command('firewall:prum_older_than_twentyfour_hours')->everyMinute()->runInBackground();

        // čistení tabulky failed_jobs
        $schedule->command('queue:flush')->daily()->runInBackground();

        // vytváření náhledů pro streamy
        $schedule->command('command:create_thumbnail_from_stream')->everyThreeMinutes()->runInBackground();

        // overování zda sluzby fungují jak mají
        $schedule->command('stream:check_if_serverices_running')->everyMinute()->runInBackground();

        // spustení všech diagnostic a náhledů, které ještě nefungují nebo z nějakého duvodu crashnuly a je zapotřebí je znovu spustit
        $schedule->command('stream:start_all_streams_for_diagnostic')->everyMinute()->runInBackground();

        // spustení streamů, které mají status error
        // $schedule->command('stream:try_start_error_stream')->everyMinute()->runInBackground();

        // relatime kontrola, pomocná fn k start_all_streams_for_diagnostic
        // $schedule->command('command:realtime_check_stream_runner')->everyMinute()->runInBackground();

        // kontrola zda funguje websocekt server
        $schedule->command('command:check_websocket')->everyMinute()->runInBackground();

        // kontrola zda funguje redis server
        $schedule->command('command:check_redis')->everyMinute()->runInBackground();

        // kontrola zda funguje queue a případě jej spustí
        $schedule->command('command:check_queue')->everyMinute()->runInBackground();

        // kotrola zda funguje selfCheck
        // $schedule->command('command:check_selfCheck')->everyMinute()->runInBackground();

        // cistení queue tabulky, kvuli performance issues,
        // pokud je mále cpu, tak se nespoustejí dostatatecne rychle queues a zbytecne se hromadí tabulky , implementace od verze jádra 0.4
        // cistení probehne kazdou druhou minutu
        $schedule->command('queue:clear')->everyMinute()->runInBackground();

        // odeslání email notifikace o nefunkčním mailu
        $schedule->command('command:SendErrorStreamEmail')->everyMinute()->runInBackground();

        // statistický blok
        // statistika streamů
        $schedule->command('stream:take_count_of_working_streams')->everyMinute()->runInBackground();
        // system -> peridické získávání dat
        $schedule->command('system:get_periodicaly_data')->everyMinute()->runInBackground();
        // cistení tabulek z systemHistory
        $schedule->command('systemHistory:prum_older_than_twelve_hours')->everyMinute()->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
