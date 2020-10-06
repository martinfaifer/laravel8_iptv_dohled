<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use Illuminate\Console\Command;

class check_if_serverices_running extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check_if_serverices_running';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ověření zda všechny sluzby u streamu fungují, pokud nefungují, dojde k pozastavení veškerých služeb u streamu';

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
        StreamController::check_if_streams_running_corectly();
    }
}
