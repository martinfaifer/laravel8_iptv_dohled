<?php

namespace App\Console\Commands;

use App\Http\Controllers\SystemProccessController;
use Illuminate\Console\Command;

class check_redis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check_redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kontrola zda funguje redis server, pokud ne, spustí se';

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
        SystemProccessController::check_if_running_redis_server();
    }
}
