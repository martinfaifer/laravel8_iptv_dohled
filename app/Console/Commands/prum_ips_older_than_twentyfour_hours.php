<?php

namespace App\Console\Commands;

use App\Http\Controllers\FirewallLogController;
use Illuminate\Console\Command;

class prum_ips_older_than_twentyfour_hours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firewall:prum_older_than_twentyfour_hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz na postupné odmazávání záznamů z forewallu, které jsou starší než 24h';

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
        // FirewallLogController::prum_ips_older_than_twentyfour_hours();
    }
}
