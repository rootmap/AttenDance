<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftSwapLoopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_swap_loops', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('shift_start');
			$table->unsignedInteger('shift_end');
			$table->unsignedInteger('swap_after_days');
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
        Schema::dropIfExists('shift_swap_loops');
    }
}
