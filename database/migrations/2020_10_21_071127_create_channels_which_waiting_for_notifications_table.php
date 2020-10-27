<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsWhichWaitingForNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channels_which_waiting_for_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->index();
            $table->string('notified')->default("false");
            $table->string('whenToNotify');
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
        Schema::dropIfExists('channels_which_waiting_for_notifications');
    }
}
