<?php

namespace App\Jobs;

use App\Http\Controllers\CcErrorController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class add_ccError implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $streamId;
    protected $error;
    protected $pozice;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $streamId, string $error, string $pozice)
    {
        $this->streamId = $streamId;
        $this->error = $error;
        $this->pozice = $pozice;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        CcErrorController::store_ccError($this->streamId, $this->error, $this->pozice);
    }
}
