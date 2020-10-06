<?php

namespace App\Console\Commands;

use App\Http\Controllers\FfmpegController;
use Illuminate\Console\Command;

class kill_running_ffmpegs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kill_running_ffmpegs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'killnutí všech ffmpegu, které vytvářejí náhled pro streamy';

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
        FfmpegController::kill_running_ffmpegs();
    }
}
