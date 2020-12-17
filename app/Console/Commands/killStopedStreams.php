<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use App\Models\StopedStream;
use App\Models\Stream;
use Illuminate\Console\Command;

class killStopedStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:killStopedStreams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Killnutí streamů, které existují v tabulce stoppedStreams a změní status na "stop"';

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
        if (StopedStream::first()) {
            foreach (StopedStream::get() as $stoppedStream) {
                $stream = Stream::where('id', $stoppedStream->streamId)->first();
                if (!is_null($stream->process_pid)) {
                    // killnutí procesu
                    StreamController::stop_diagnostic_stream_from_backend($stream->process_pid);
                    // změna statusu
                    Stream::where('id', $stoppedStream->streamId)
                        ->update(['status' => "stop", 'process_pid' => null]);
                }
            }
        }
    }
}
