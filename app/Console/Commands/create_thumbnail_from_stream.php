<?php

namespace App\Console\Commands;

use App\Http\Controllers\FfmpegController;
use App\Jobs\FFmpegImageCreate;
use App\Models\Stream;
use Illuminate\Console\Command;

class create_thumbnail_from_stream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:create_thumbnail_from_stream';

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
        Stream::where('status', "success")->orWhere('status', "issue")->get()->each(function ($stream) {
            FfmpegController::find_image_if_exist_delete_and_create_new($stream['id'], $stream['stream_url'], $stream['image']);
        });
    }
}
