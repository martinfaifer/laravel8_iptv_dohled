<?php

namespace App\Jobs;

use App\Http\Controllers\FfmpegController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Traits\FfmpegTrait;

class FFmpegImageCreate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use FfmpegTrait;


    protected $stream;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->ffmpeg_create_image($this->stream);
    }
}
