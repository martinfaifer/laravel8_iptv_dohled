<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_histories', function (Blueprint $table) {
            $table->id();
            $table->string('value')->index();
            $table->string('value_type')->index(); // ram, system load (avarage 5min), swap, hdd, streams ( počet funkčních / nefunkčních )
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
        Schema::dropIfExists('system_histories');
    }
}
