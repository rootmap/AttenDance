<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadAttendanceFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_attendance_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('upload_attendance_setting_id');
            $table->unsignedInteger('machine_index');
            $table->unsignedInteger('raw_employee_code_index');
            $table->unsignedInteger('raw_date_index');
            $table->unsignedInteger('raw_time_index');
            $table->foreign('upload_attendance_setting_id')->references('id')->on('upload_attendance_settings');
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
        Schema::dropIfExists('upload_attendance_fields');
    }
}
