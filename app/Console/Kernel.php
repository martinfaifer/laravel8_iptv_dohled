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
        // overování zda sluzby fungují jak mají
        $schedule->command('command:check_if_serverices_running')->everyMinute()->runInBackground();

        // spustení všech diagnostic a náhledů, které ještě nefungují nebo z nějakého duvodu crashnuly a je zapotřebí je znovu spustit
        $schedule->command('command:start_all_streams_for_diagnostic')->everyMinute()->runInBackground();

        // kontrola zda funguje redis server
        $schedule->command('command:check_redis')->everyMinute()->runInBackground();

        // kontrola zda funguje queue a případě jej spustí
        $schedule->command('command:check_queue')->everyMinute()->runInBackground();

        // cistení queue tabulky, kvuli performance issues,
        // pokud je mále cpu, tak se nespoustejí dostatatecne rychle queues a zbytecne se hromadí tabulky , implementace od verze jádra 0.4
        // cistení probehne kazdou druhou minutu
        $schedule->command('queue:clear')->everyTwoMinutes()->runInBackground();
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
