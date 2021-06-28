<?php

namespace App\Console;

use App\Models\SystemSetting;
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

        if (SystemSetting::where('modul', "CRON")->first()->stav === "on") {

            // PODMÍNKA PRO SPUSTENÍ CRONU -> SEEDER CRON_STATUS === ON || OFF
            // DIAGNOSTIKA
            if (SystemSetting::where('modul', 'ffprobe')->first()->stav === "on") {
                $schedule->command('ffprobe:audio_video_check')->everyFifteenMinutes()->runInBackground();
            }
            if (SystemSetting::where('modul', 'stream_diagnostic')->first()->stav === "on") {
                // spustení všech diagnostic a náhledů, které ještě nefungují nebo z nějakého duvodu crashnuly a je zapotřebí je znovu spustit
                $schedule->command('stream:start_all_streams_for_diagnostic')->everyMinute()->runInBackground();
            }
            // spustení streamů, kterým přestal existoval pid
            $schedule->command('stream:try_start_crashed_stream')->everyMinute()->runInBackground();

            // ------------------------------------------------------------------------------------------------------------------------------------

            if (SystemSetting::where('modul', 'create_thumbnails')->first()->stav === "on") {
                // SPRÁVA NÁHLEDŮ
                $schedule->command('ffmpeg:create_thumbnail_from_stream')->everyFiveMinutes()->runInBackground();
            }
            // odebrání starých náhledů
            $schedule->command('command:deleteImagesOlderTharOneHour')->hourly()->runInBackground();
            // ------------------------------------------------------------------------------------------------------------------------------------

            // SCHEDULER
            $schedule->command('command:streamScheduler')->everyMinute()->runInBackground();
            // ------------------------------------------------------------------------------------------------------------------------------------

            // SYSTEM
            $schedule->command('system:get_periodicaly_data')->everyMinute()->runInBackground();
            // $schedule->command('stream:check_if_serverices_running')->everyMinute()->runInBackground();
            // ------------------------------------------------------------------------------------------------------------------------------------

            // PRUM
            // $schedule->command('stream:prum_CC_errors')->everyMinute()->runInBackground()->withoutOverlapping();
            // $schedule->command('firewall:prum_older_than_twentyfour_hours')->everyMinute()->runInBackground();
            // $schedule->command('queue:flush')->daily()->runInBackground();
            // $schedule->command('queue:clear')->everyFifteenMinutes()->runInBackground();
            // $schedule->command('systemAndStreamHistory:prum_older_than_twelve_hours')->everyMinute()->runInBackground();
            // ------------------------------------------------------------------------------------------------------------------------------------

            // NOTIFIKACE -> EMAILS
            if (SystemSetting::where('modul', 'email_sending')->first()->stav === "on") {
                $schedule->command('email:SendErrorStreamEmail')->everyMinute()->runInBackground();
                $schedule->command('email:SendSuccessEmail')->everyMinute()->runInBackground();
            }

            if (SystemSetting::where('modul', 'slack_sending')->first()->stav === "on") {
                $schedule->command('slack:notification')->everyMinute()->runInBackground();
            }
            // ------------------------------------------------------------------------------------------------------------------------------------

            // killnutí streamu, který existuje v tabulce stoppedStreams
            // $schedule->command('command:killStopedStreams')->everyMinute()->runInBackground();
        }
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
