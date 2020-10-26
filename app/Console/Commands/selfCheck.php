<?php

namespace App\Console\Commands;

use App\Http\Controllers\SystemController;
use Illuminate\Console\Command;

class selfCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:selfCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz na spustění selfchecku na monitorování stavu Hardwaru';

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
        SystemController::selfCheck();
    }
}
