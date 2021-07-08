<?php

namespace App\Console\Commands;

use App\Http\Controllers\System\SystemController;
use Illuminate\Console\Command;

class deleteImagesOlderTharOneHour extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:deleteImagesOlderTharOneHour';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Odebrání náhledá, které jsou starší než jedna hodina ze slozky /storage/channelsImage/';

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
        SystemController::oldImgOlderThanOneHour();
    }
}
