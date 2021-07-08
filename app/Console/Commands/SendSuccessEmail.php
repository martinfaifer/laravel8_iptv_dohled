<?php

namespace App\Console\Commands;

use App\Http\Controllers\Notifications\EmailNotificationController;
use Illuminate\Console\Command;
use PharIo\Manifest\Email;

class SendSuccessEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:SendSuccessEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Odeslání emalových notifikací o kanálech, které nefungovali a nyní jsou OK';

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
        EmailNotificationController::notify_success_stream();
    }
}
