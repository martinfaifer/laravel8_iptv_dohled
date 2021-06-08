<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use Illuminate\Console\Command;

class take_count_of_working_streams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:take_count_of_working_streams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz pro výpočet aktivních || nekatnivních streamů , kontrola probíhá kazdou minutu';

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
        StreamController::take_count_of_working_streams();
    }
}
