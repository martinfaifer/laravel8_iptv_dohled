<?php

namespace App\Console\Commands;

use App\Http\Controllers\SystemController;
use Illuminate\Console\Command;

class checkPid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkPid {pid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        return SystemController::check_if_process_running($this->argument('pid'));
    }
}
