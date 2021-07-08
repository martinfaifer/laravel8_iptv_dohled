<?php

namespace App\Console\Commands;

use App\Http\Controllers\Diagnostic\DiagnosticController;
use Illuminate\Console\Command;

class start_realtime_diagnostic_and_return_pid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:start_realtime_diagnostic_and_return_pid {streamurl} {streamId} {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spustí diagnostikování streamu na pozadí, za příkazen se zasílá  objekt stream';

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
        DiagnosticController::stream_realtime_diagnostic_and_return_status($this->argument('streamurl'), $this->argument('streamId'));
    }
}
