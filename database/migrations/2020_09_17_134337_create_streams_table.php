<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('nazev')->index();
            $table->string('stream_url')->index();
            $table->string('image')->default('false');
            // $table->string('pcrPid')->nullable();
            $table->longText('dokumentaceUrl')->nullable();
            $table->string('dokumentaceId')->nullable();
            $table->boolean('dohledovano')->default(false)->index();
            $table->boolean('dohledVidea')->default(false)->index();
            $table->boolean('dohledVolume')->default(false)->index();
            $table->boolean('dohledAudia')->default(false)->index();
            $table->boolean('dohledBitrate')->default(false)->index();
            $table->boolean('vytvaretNahled')->default(false)->index();
            $table->boolean('sendMailAlert')->default(false)->index();
            $table->boolean('sendSmsAlert')->default(false)->index();
            $table->string('status')->default('waiting')->index();
            $table->string('process_pid')->nullable();
            $table->string('ffmpeg_pid')->nullable();
            $table->string('socket_process_pid')->nullable();
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
        Schema::dropIfExists('streams');
    }
}
