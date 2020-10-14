<?php

namespace App\Jobs;

use App\Events\StreamInfoTsVideoBitrate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StreamInfoTsVideoBitrateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $streamId;
    protected $videoBitrate;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($streamId, $videoBitrate)
    {
        $this->streamId = $streamId;
        $this->videoBitrate = $videoBitrate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // event(new StreamInfoTsVideoBitrate($this->streamId, $this->videoBitrate));
    }
}
