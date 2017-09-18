<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->text('emp_code')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->date('company_effective_start_date')->nullable();
            $table->date('company_effective_end_date')->nullable();
            $table->date('proposed_confirmation_date')->nullable();
            $table->boolean('is_pf_eligible')->nullable();
            $table->date('pf_effective_from')->nullable();
            $table->unsignedInteger('status')->nullable();
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
        Schema::dropIfExists('employee_companies');
    }
}
