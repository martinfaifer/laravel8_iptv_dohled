<?php

namespace App\Console\Commands;

use App\Http\Controllers\Diagnostic\StreamDiagnosticController;
use Illuminate\Console\Command;

class try_start_crashed_stream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:try_start_crashed_stream';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz pro spustení streamu, kterému prestala fungovat diagnostika';

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
        StreamDiagnosticController::check_if_streams_running();
    }
}
