<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use Illuminate\Console\Command;

class kill_all_running_streams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kill_all_running_streams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz pro ukončení všech streamů, co aktuálně fungují';

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
        return StreamController::kill_all_running_streams();
    }
}
