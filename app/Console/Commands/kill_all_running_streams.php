<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamDiagnosticController;
use Illuminate\Console\Command;

class kill_all_running_streams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streams:kill_all_running_streams';

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
        StreamDiagnosticController::kill_all_running_streams();
        // vycistení queue tabulky po ukoncení vsech streamů => implementace od verze jádra 0.4
        shell_exec('php artisan queue:clear');
    }
}
