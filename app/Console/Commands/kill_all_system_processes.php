<?php

namespace App\Console\Commands;

use App\Http\Controllers\SystemProccessController;
use Illuminate\Console\Command;

class kill_all_system_processes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kill_all_system_processes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Killnutí všech systémových procesů, pro případ nouze';

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
        SystemProccessController::kill_all_processes();
    }
}
