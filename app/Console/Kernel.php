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

            // DIAGNOSTIKA
            $schedule->command('ffprobe:audio_video_check')->everyFifteenMinutes()->runInBackground();

            if (SystemSetting::where('modul', 'stream_diagnostic')->first()->stav === "on") {
                // spustení všech diagnostic a náhledů, které ještě nefungují nebo z nějakého duvodu crashnuly a je zapotřebí je znovu spustit
                $schedule->command('stream:start_all_streams_for_diagnostic')->everyMinute()->runInBackground();
            }
            // spustení streamů, kterým přestal existoval pid
            $schedule->command('stream:try_start_crashed_stream')->everyMinute()->runInBackground();

            // ------------------------------------------------------------------------------------------------------------------------------------


            // SPRÁVA NÁHLEDŮ
            $schedule->command('ffmpeg:create_thumbnail_from_stream')->everyThreeMinutes()->runInBackground();

            // odebrání starých náhledů
            $schedule->command('command:deleteImagesOlderTharOneHour')->hourly()->runInBackground();
            // ------------------------------------------------------------------------------------------------------------------------------------

            // SCHEDULER
            $schedule->command('command:streamScheduler')->everyMinute()->runInBackground();
            // ------------------------------------------------------------------------------------------------------------------------------------

            // SYSTEM
            $schedule->command('system:get_periodicaly_data')->everyMinute()->runInBackground();
            // ------------------------------------------------------------------------------------------------------------------------------------

            // PRUM
            $schedule->command('queue:flush')->everyMinute()->runInBackground()->withoutOverlapping();
            // $schedule->command('systemAndStreamHistory:prum_older_than_twelve_hours')->everyMinute()->runInBackground();
            // ------------------------------------------------------------------------------------------------------------------------------------

            // NOTIFIKACE -> EMAILS
            if (SystemSetting::where('modul', 'email_sending')->first()->stav === "on") {
                $schedule->command('email:SendErrorStreamEmail')->everyMinute()->runInBackground()->withoutOverlapping();
                $schedule->command('email:SendSuccessEmail')->everyMinute()->runInBackground()->withoutOverlapping();
            }

            if (SystemSetting::where('modul', 'slack_sending')->first()->stav === "on") {
                $schedule->command('slack:notification')->everyMinute()->runInBackground();
            }
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
