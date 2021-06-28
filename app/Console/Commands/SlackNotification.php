<?php

namespace App\Console\Commands;

use App\Http\Controllers\SlackController;
use Illuminate\Console\Command;

class SlackNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slack:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Odeslání Slack notifikace o stavu streamu';

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
        SlackController::notify();
    }
}
