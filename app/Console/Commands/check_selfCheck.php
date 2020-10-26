<?php

namespace App\Console\Commands;

use App\Http\Controllers\SystemProccessController;
use Illuminate\Console\Command;

class check_selfCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check_selfCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kontrola, zda funguje selfCheck a vrácení pidu';

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
        SystemProccessController::check_if_self_check_running();
    }
}
