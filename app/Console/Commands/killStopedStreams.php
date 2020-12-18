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
    protected $description = 'Killnutí streamů, které mají status dohledováno = false a změna statusu na "stop"';

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
        // if (StopedStream::first()) {
        //     foreach (StopedStream::get() as $stoppedStream) {
        //         $stream = Stream::where('id', $stoppedStream->streamId)->first();
        //         if (!is_null($stream->process_pid)) {
        //             // killnutí procesu
        //             // StreamController::stop_diagnostic_stream_from_backend($stream->process_pid);
        //             // změna statusu
        //             Stream::where('id', $stoppedStream->streamId)
        //                 ->update(['status' => "stop", 'process_pid' => null]);
        //         }
        //     }
        // }

        if (Stream::where('dohledovano', false)->where('status', "!=", 'stop')->first()) {
            foreach (Stream::where('dohledovano', false)->where('status', "!=", 'stop')->get() as $streamToStop) {
                // oveření, že není process_pid = null
                if (!is_null($streamToStop->process_pid)) {
                    StreamController::stop_diagnostic_stream_from_backend($streamToStop->process_pid);

                    Stream::where('id', $streamToStop->id)
                        ->update(['status' => "stop", 'process_pid' => null]);
                }
            }
        }
    }
}
