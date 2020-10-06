<?php

namespace App\Jobs;

use App\Http\Controllers\FFprobeController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FFprobeDiagnostic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $streamUrl;
    protected $streamId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($streamUrl, $streamId)
    {
        $this->streamUrl = $streamUrl;
        $this->streamId = $streamId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        FFprobeController::ffprobe_diagnostic($this->streamUrl, $this->streamId);
    }
}
