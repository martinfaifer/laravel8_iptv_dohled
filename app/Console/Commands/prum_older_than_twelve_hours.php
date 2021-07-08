<?php

namespace App\Console\Commands;

use App\Http\Controllers\System\SystemHistoryController;
use Illuminate\Console\Command;

class prum_older_than_twelve_hours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'systemAndStreamHistory:prum_older_than_twelve_hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        SystemHistoryController::prum_older_than_twelve_hours();
    }
}
