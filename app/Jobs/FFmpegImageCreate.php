<?php

namespace App\Jobs;

use App\Http\Controllers\FfmpegController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FFmpegImageCreate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $streamId;
    protected $streamUrl;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($streamId, $streamUrl)
    {
        $this->streamId = $streamId;
        $this->streamUrl = $streamUrl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        FfmpegController::find_image_if_exist_delete_and_create_new($this->streamId, $this->streamUrl);
    }
}
