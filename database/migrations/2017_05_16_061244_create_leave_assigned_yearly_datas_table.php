<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveAssignedYearlyDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_assigned_yearly_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('emp_code');
            $table->unsignedInteger('leave_policy_id');
            $table->unsignedInteger('year');
            $table->unsignedInteger('total_days');
            $table->unsignedInteger('availed_days');
            $table->unsignedInteger('remaining_days');
            $table->float('carry_forward_balance');
            $table->unsignedInteger('incash_balance');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('leave_policy_id')->references('id')->on('leave_policies');
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
        Schema::dropIfExists('leave_assigned_yearly_datas');
    }
}
