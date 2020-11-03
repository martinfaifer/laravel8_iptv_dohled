<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamSheduleFromIptvDokusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stream_shedule_from_iptv_dokus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('streamId')->index();
            $table->string('start_day')->index();
            $table->string('start_time')->index();
            $table->string('end_day')->index();
            $table->string('end_time')->index();
            $table->string('every_day')->index();
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
        Schema::dropIfExists('stream_shedule_from_iptv_dokus');
    }
}
