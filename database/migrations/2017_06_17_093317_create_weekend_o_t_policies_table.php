<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeekendOTPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekend_o_t_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->boolean('is_ot_count_as_total_working_hour');
            $table->boolean('is_ot_will_start_after_fix_hour');
            $table->time('hour_after');
			$table->boolean('is_standard_max_ot_hour');
            $table->time('standard_max_ot_hour');
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
        Schema::dropIfExists('weekend_o_t_policies');
    }
}
