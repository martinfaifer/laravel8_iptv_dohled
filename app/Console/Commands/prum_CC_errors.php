<?php

namespace App\Console\Commands;

use App\Http\Controllers\CcErrorController;
use Illuminate\Console\Command;

class prum_CC_errors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:prum_CC_errors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Postupné odmazávání záznamů z tabulky cc_errors';

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
        CcErrorController::prum_every_two_hours();
    }
}
