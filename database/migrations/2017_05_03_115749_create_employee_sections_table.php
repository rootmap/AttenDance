<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->text('emp_code')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->date('section_effective_start_date')->nullable();
            $table->date('section_effective_end_date')->nullable();
            $table->unsignedInteger('status');
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
        Schema::dropIfExists('employee_sections');
    }
}
