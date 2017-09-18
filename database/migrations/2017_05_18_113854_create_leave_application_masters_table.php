<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveApplicationMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_application_masters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('emp_code');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('leave_policy_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days_applied',3,2);
            $table->boolean('is_half_day');
            $table->enum('half_day',array('Not Applicable','1st Half','2nd Half'));
            $table->enum('leave_status',array('Pending','Reject','Approved'));
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
        Schema::dropIfExists('leave_application_masters');
    }
}
