<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Traits\FfmpegTrait;

class FFProbeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FfmpegTrait;

    protected $stream_id;
    protected $stream_url;

    /**
     *
     * @param integer $stream_id
     * @param string $stream_url
     */
    public function __construct(int $stream_id, string $stream_url)
    {
        $this->stream_id = $stream_id;
        $this->stream_url = $stream_url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ffprobe = $this->ffprobe($this->stream_url);
        if (!empty($ffprobe)) {
            $this->check_if_stream_is_resync_audio_video($ffprobe, $this->stream_id);
        }
    }
}
