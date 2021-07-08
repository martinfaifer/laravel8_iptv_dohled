<?php

namespace App\Console\Commands;

use App\Http\Controllers\System\SystemController;
use Illuminate\Console\Command;

class CheckWebCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CheckWebCertificate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'zobrazení expirace certifikatu';

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
        SystemController::check_web_certificate();
    }
}
