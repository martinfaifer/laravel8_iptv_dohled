<?php

namespace App\Console\Commands;

use App\Http\Controllers\SystemProccessController;
use Illuminate\Console\Command;

class check_queue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check_queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz na kontrolu qeuue, pokud nefunguje spusti jej automaticky';

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
        SystemProccessController::check_if_running_queue_worker_and_if_not_start_and_return_pid();
    }
}
