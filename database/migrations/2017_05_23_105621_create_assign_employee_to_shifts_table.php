<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignEmployeeToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assign_employee_to_shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
//            $table->unsignedInteger('department_id');
//            $table->unsignedInteger('section_id');
//            $table->unsignedInteger('designation_id');
            $table->unsignedInteger('shift_id');
            $table->text('emp_code');
            $table->date('start_date');
            $table->date('end_date');
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
        Schema::dropIfExists('assign_employee_to_shifts');
    }
}
