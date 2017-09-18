<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftPatternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_patterns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('shift_id');
            $table->string('name');
            $table->time('start_in_time_pattern');
            $table->time('end_in_time_pattern');
            $table->time('start_out_time_pattern');
            $table->time('end_out_time_pattern');
            $table->foreign('shift_id')->references('id')->on('shifts');
            $table->foreign('company_id')->references('id')->on('companies');
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
        Schema::dropIfExists('shift_patterns');
    }
}
