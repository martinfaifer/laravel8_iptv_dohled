<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stream_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->index();
            $table->string('tsid')->nullable();
            $table->string('pmtpid')->nullable();
            $table->string('pcrpid')->nullable();
            $table->string('provider')->nullable();
            $table->string('name')->nullable();
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
        Schema::dropIfExists('stream_services');
    }
}
