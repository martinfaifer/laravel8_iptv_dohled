<?php

namespace App\Console\Commands;

use App\Http\Controllers\SystemController;
use Illuminate\Console\Command;

class delete_failed_jobs_table extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:delete_failed_jobs_table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mazání tabulky failed_jobs';

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
        SystemController::clear_jobs_failed_table();
    }
}
