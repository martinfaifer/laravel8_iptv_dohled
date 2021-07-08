<?php

namespace App\Console\Commands;

use App\Http\Controllers\System\SystemProccessController;
use Illuminate\Console\Command;

class realtime_check_stream_runner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:realtime_check_stream_runner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'realtime overení, že streamy funguji jak mají';

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
        SystemProccessController::check_if_streams_running_correctly();
    }
}
