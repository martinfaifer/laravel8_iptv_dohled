<?php

namespace App\Jobs;

use App\Http\Controllers\StreamController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Diagnostic_Stream_update implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $stream;
    protected $status;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($stream, $status)
    {
        $this->stream = $stream;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        StreamController::queue_diagnostic_update_stream_status($this->stream, $this->status);
    }
}
