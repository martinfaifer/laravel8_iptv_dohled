<?php

namespace App\Jobs;

use App\Http\Controllers\EmailNotificationController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSuccessEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $streamId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($streamId)
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
        EmailNotificationController::notify_success_stream($this->streamId);
    }
}
