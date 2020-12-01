<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use Illuminate\Console\Command;

class openStreamSocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:openStreamSocket {streamId} {killSignal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Příkaz pro otevrení socketu nebo jeho ukoncení, zároven hlídá, zda jiz neexistuje process na pozadí';

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
        StreamController::hold_multicast_per_stream(
            $this->argument('streamId'),
            $this->argument('killSignal')
        );
    }
}
