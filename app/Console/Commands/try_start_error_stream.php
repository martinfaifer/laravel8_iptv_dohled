<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use Illuminate\Console\Command;

class try_start_error_stream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:try_start_error_stream';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vyhledá streamy se statusem error a pokusí se je znovu spustit';

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
        StreamController::try_start_error_stream();
    }
}
