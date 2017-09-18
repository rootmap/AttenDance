<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendancePoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('policy_title');
            $table->time('office_start_time');
            $table->time('office_end_time');
            $table->time('entry_buffer_time');
            $table->time('total_hours');
            $table->boolean('is_halfday_applicable');
            $table->string('half_day_name');
            $table->time('half_day_office_end_time');
            $table->time('half_day_total_working_hour');
            $table->boolean('is_ot_applicable');
            $table->boolean('is_ot_buffer_time');
            $table->time('ot_buffer_time');
            $table->boolean('is_ot_max_active');
            $table->time('max_ot_hour');
            $table->boolean('is_active');
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
        Schema::dropIfExists('attendance_policies');
    }
}
