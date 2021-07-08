<?php

namespace App\Jobs;

use App\Http\Controllers\Notifications\EmailNotificationController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SystemMailAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $partOfSystem;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($partOfSystem)
    {
        $this->partOfSystem = $partOfSystem;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        EmailNotificationController::send_system_warning($this->partOfSystem);
    }
}
