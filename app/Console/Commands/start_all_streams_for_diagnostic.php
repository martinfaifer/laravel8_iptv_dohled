<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamDiagnosticController;
use Illuminate\Console\Command;

class start_all_streams_for_diagnostic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:start_all_streams_for_diagnostic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz, který spustí všechny streamy, které nemají status stop a process_pid = null a ffmpeg_pid = null';

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
        StreamDiagnosticController::start_streams_for_diagnostic();
    }
}
