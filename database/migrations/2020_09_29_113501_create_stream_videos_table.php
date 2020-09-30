<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stream_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->index();
            $table->integer('pid')->nullable();
            $table->integer('bitrate')->nullable();
            $table->string('access')->nullable();
            $table->string('discontinuities')->nullable();
            $table->string('scrambled')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stream_videos');
    }
}
