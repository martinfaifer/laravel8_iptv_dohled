<?php

namespace App\Console\Commands;

use App\Http\Controllers\FfmpegController;
use App\Jobs\FFmpegImageCreate;
use App\Models\Stream;
use Illuminate\Console\Command;
use App\Traits\FfmpegTrait;

class create_thumbnail_from_stream extends Command
{
    use FfmpegTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ffmpeg:create_thumbnail_from_stream';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'vytvoření náhledů z funkčních streamů, které mají povolené vytváření náhledů';

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
        Stream::where([['status', "running"], ['is_problem', false], ['vytvaretNahled', true]])->get()->each(function ($stream) {
            $this->ffmpeg_create_image($stream);
        });
    }
}
