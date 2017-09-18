<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveWorkFlowSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_work_flow_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("company_id");
            $table->string("emp_code");
            $table->string("sup_emp_code");
            $table->unsignedInteger("step");
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
        Schema::dropIfExists('leave_work_flow_settings');
    }
}
