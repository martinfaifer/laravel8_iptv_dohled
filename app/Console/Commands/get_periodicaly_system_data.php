<?php

namespace App\Console\Commands;

use App\Http\Controllers\Streams\StreamController;
use App\Http\Controllers\System\SystemController;
use App\Http\Controllers\System\SystemCpuController;
use Illuminate\Console\Command;

class get_periodicaly_system_data extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:get_periodicaly_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz pro získávání peridických dat ze strany serveru';

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
        SystemController::get_periodicly_systemLoad_ram_hdd_swap();
        SystemCpuController::store_cpu_usage();
        StreamController::take_count_of_working_issued_stopped_streams();
    }
}
