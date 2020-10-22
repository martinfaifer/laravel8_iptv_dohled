<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmailNotificationController;
use Illuminate\Console\Command;

class SendErrorStreamEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SendErrorStreamEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Odeslnání emailu s informací o nefunkčním streamu se spožděním 5min po výpadku';

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
        EmailNotificationController::notify_crashed_stream();
    }
}
