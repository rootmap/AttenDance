<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeCompanyBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_company_branches', function (Blueprint $table) {
            $table->increments('id');
            $table->text('emp_code')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('branch_id')->nullable();
            $table->date('branch_effective_start_date')->nullable();
            $table->date('branch_effective_end_date')->nullable();
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
        Schema::dropIfExists('employee_company_branches');
    }
}
