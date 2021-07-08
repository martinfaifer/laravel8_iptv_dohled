<?php

namespace App\Jobs;

use App\Http\Controllers\API\ApiController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RestartStreamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $streamId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $streamId)
    {
        $this->streamId = $streamId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ApiController::send_req_for_restart_stream_to_dokumentation($this->streamId);
    }
}
