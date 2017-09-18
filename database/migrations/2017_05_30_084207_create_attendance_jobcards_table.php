<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendanceJobcardsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('attendance_jobcards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');

            $table->string('emp_code');

            $table->date('start_date');
            $table->date('end_date');

            $table->time('admin_in_time');
            $table->time('admin_out_time');
            $table->time('admin_total_time');
            $table->time('admin_total_ot');
            $table->string('admin_day_status');

            $table->time('user_in_time');
            $table->time('user_out_time');
            $table->time('user_total_time');
            $table->time('user_total_ot');
            $table->string('user_day_status');

            $table->time('audit_in_time');
            $table->time('audit_out_time');
            $table->time('audit_total_time');
            $table->time('audit_total_ot');
            $table->string('audit_day_status');

            $table->boolean('edit_flag');
            $table->boolean('reprocess_flag');
            $table->string('edited_emp_code');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('attendance_jobcards');
    }

}
