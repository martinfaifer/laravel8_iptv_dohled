<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamDiagnosticController;
use Illuminate\Console\Command;

class ffprobe_audio_video_check extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ffprobe:audio_video_check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spuštění FFPROBE pro kontrolu synchronizace audia videa';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        StreamDiagnosticController::check_stream_audio_video();
    }
}
