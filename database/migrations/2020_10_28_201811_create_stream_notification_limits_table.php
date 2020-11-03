<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamNotificationLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stream_notification_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->index();
            $table->integer('video_bitrate')->default(0);
            $table->integer('video_discontinuities')->default(2);
            $table->integer('video_scrambled')->default(2);
            $table->integer('audio_discontinuities')->default(2);
            $table->integer('audio_scrambled')->default(2);
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
        Schema::dropIfExists('stream_notification_limits');
    }
}
