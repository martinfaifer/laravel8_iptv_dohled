<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamTSdatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stream_t_sdatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->index();
            $table->string('invalidsyncs')->nullable();
            $table->string('scrambledpids')->nullable();
            $table->string('transporterrors')->nullable();
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
        Schema::dropIfExists('stream_t_sdatas');
    }
}
