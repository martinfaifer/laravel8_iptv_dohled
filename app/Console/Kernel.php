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
        // PODMÍNKA PRO SPUSTENÍ CRONU -> SEEDER CRON_STATUS === ON || OFF

        // DIAGNOSTIKA
        $schedule->command('ffprobe:audio_video_check')->everyFifteenMinutes()->runInBackground();
        // spustení všech diagnostic a náhledů, které ještě nefungují nebo z nějakého duvodu crashnuly a je zapotřebí je znovu spustit
        $schedule->command('stream:start_all_streams_for_diagnostic')->everyMinute()->runInBackground();
        // ------------------------------------------------------------------------------------------------------------------------------------

        // SPRÁVA NÁHLEDŮ
        $schedule->command('command:create_thumbnail_from_stream')->everyThreeMinutes()->runInBackground();
        // odebrání starých náhledů
        $schedule->command('command:deleteImagesOlderTharOneHour')->hourly()->runInBackground();
        // ------------------------------------------------------------------------------------------------------------------------------------

        // SCHEDULER
        $schedule->command('command:streamScheduler')->everyMinute()->runInBackground();
        // ------------------------------------------------------------------------------------------------------------------------------------

        // SYSTEM
        $schedule->command('system:get_periodicaly_data')->everyMinute()->runInBackground();
        $schedule->command('stream:check_if_serverices_running')->everyMinute()->runInBackground();
        // ------------------------------------------------------------------------------------------------------------------------------------

        // PRUM
        $schedule->command('stream:prum_CC_errors')->everyMinute()->runInBackground()->withoutOverlapping();
        $schedule->command('firewall:prum_older_than_twentyfour_hours')->everyMinute()->runInBackground();
        $schedule->command('queue:flush')->daily()->runInBackground();
        $schedule->command('queue:clear')->everyFifteenMinutes()->runInBackground();
        $schedule->command('systemAndStreamHistory:prum_older_than_twelve_hours')->everyMinute()->runInBackground();
        // ------------------------------------------------------------------------------------------------------------------------------------

        // NOTIFIKACE
        $schedule->command('command:SendErrorStreamEmail')->everyMinute()->runInBackground();
        // ------------------------------------------------------------------------------------------------------------------------------------

        // STATISTIKA
        $schedule->command('chart:take_count_of_working_streams')->everyMinute()->runInBackground();
        // ------------------------------------------------------------------------------------------------------------------------------------

        // killnutí streamu, který existuje v tabulce stoppedStreams
        // $schedule->command('command:killStopedStreams')->everyMinute()->runInBackground();


        // spustení streamů, které mají status error
        // $schedule->command('stream:try_start_error_stream')->everyMinute()->runInBackground();

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
