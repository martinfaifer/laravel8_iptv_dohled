<?php

namespace App\Console\Commands;

use App\Http\Controllers\Notifications\StreamSheduleFromIptvDokuController;
use Illuminate\Console\Command;

class streamScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:streamScheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'dohled, zda má kanál zapnutý sheduler a následně jej aplikuje';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        StreamSheduleFromIptvDokuController::check_time_to_schedule();
    }
}
