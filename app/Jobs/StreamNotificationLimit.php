<?php

namespace App\Jobs;

use App\Http\Controllers\Notifications\StreamNotificationLimitController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StreamNotificationLimit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $streamId;
    protected $video_bitrate;
    protected $video_discontinuities;
    protected $video_scrambled;
    protected $audio_discontinuities;
    protected $audio_scrambled;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($streamId, $video_bitrate, $video_discontinuities, $video_scrambled, $audio_discontinuities, $audio_scrambled)
    {
        $this->streamId = $streamId;
        $this->video_bitrate = $video_bitrate;
        $this->video_discontinuities = $video_discontinuities;
        $this->video_scrambled = $video_scrambled;
        $this->audio_discontinuities = $audio_discontinuities;
        $this->audio_scrambled = $audio_scrambled;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        StreamNotificationLimitController::check_stream_if_exist_in_table_and_check_values_and_probably_return_eventNotification(
            $this->streamId,
            $this->video_bitrate,
            $this->video_discontinuities,
            $this->video_scrambled,
            $this->audio_discontinuities,
            $this->audio_scrambled
        );
    }
}
