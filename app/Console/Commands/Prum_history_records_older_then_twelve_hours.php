<?php

namespace App\Console\Commands;

use App\Http\Controllers\Streams\StreamHistoryController;
use Illuminate\Console\Command;

class Prum_history_records_older_then_twelve_hours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streamHistory:delete_older_then_twelve_hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Odebrání záznamů z historie, které jsou starší než 12 hodin';

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
        StreamHistoryController::get_last_twelve_hours_records_last_delete();
    }
}
