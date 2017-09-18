<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveApplicationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_application_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('master_id');
            $table->unsignedInteger('leave_policy_id');
            $table->date('date');
            $table->string('emp_code');
            $table->foreign('master_id')->references('id')->on('leave_application_masters');
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
        Schema::dropIfExists('leave_application_details');
    }
}
