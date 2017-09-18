<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveApplicationApprovalFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_application_approval_flows', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('emp_code');
            $table->string('sup_emp_code');
            $table->unsignedInteger('step');
            $table->unsignedInteger('master_id');
            $table->foreign('master_id')->references('id')->on('leave_application_masters');
            $table->enum('approval_status',array('Pending','Reject','Approved'));
            $table->enum('step_flag',array('Pending','Active','Complete'));
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
        Schema::dropIfExists('leave_application_approval_flows');
    }
}
