<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayrollSalaryComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_salary_components', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('header_title');
            $table->enum('DisplayOnSalarySheet',array('Show in salary sheet', 'None'));
            $table->enum('headerDisplayOn',array('+', '-', 'None'));
            $table->boolean('is_monthly');
            $table->boolean('is_optional');
            $table->boolean('is_gross');
            $table->boolean('is_calculative');
            $table->string('field_name');
            $table->int('display_order');
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
        Schema::dropIfExists('payroll_salary_components');
    }
}
