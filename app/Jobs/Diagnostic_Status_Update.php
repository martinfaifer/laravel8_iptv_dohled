<?php

namespace App\Jobs;

use App\Http\Controllers\StreamController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Diagnostic_Status_Update implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $streamId;
    protected $arrData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($streamId, $arrData)
    {
        $this->streamId = $streamId;
        $this->arrData = $arrData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //   EmailNotificationController::notify_success_stream($this->streamId);
        // funkce na zpracování a update zaznamů
        StreamController::queue_diagnostic_update_status_and_create_more_information_about_strea($this->streamId, $this->arrData);
    }
}
