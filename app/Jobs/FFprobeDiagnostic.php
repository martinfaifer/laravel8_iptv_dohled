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
    protected $audioVideoCheck;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($streamUrl, $streamId, $audioVideoCheck)
    {
        $this->streamUrl = $streamUrl;
        $this->streamId = $streamId;
        $this->audioVideoCheck = $audioVideoCheck;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        FFprobeController::ffprobe_diagnostic($this->streamUrl, $this->streamId, $this->audioVideoCheck);
    }
}
