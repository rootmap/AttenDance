<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendanceJobcardPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_jobcard_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->boolean('is_admin_data_show_policy')->nullable();
            $table->enum('admin_addition_deduction',array('+','-'));
            $table->time('admin_with_intime');
            $table->time('admin_with_outime');
            $table->boolean('is_admin_max_ot_fixed')->nullable();
            $table->time('admin_max_ot_hour')->nullable();
            $table->boolean('is_admin_ot_adjust_with_outtime')->nullable();
            
            $table->boolean('is_user_data_show_policy')->nullable();
            $table->enum('user_addition_deduction',array('+','-'));
            $table->time('user_with_intime');
            $table->time('user_with_outime');
            $table->boolean('is_user_max_ot_fixed')->nullable();
            $table->time('user_max_ot_hour');
            $table->boolean('is_user_ot_adjust_with_outtime')->nullable();

            $table->boolean('is_audit_data_show_policy')->nullable();
            $table->enum('audit_addition_deduction',array('+','-'));
            $table->time('audit_with_intime');
            $table->time('audit_with_outime');
            $table->boolean('is_audit_max_ot_fixed')->nullable();
            $table->time('audit_max_ot_hour');
            $table->boolean('is_audit_ot_adjust_with_outtime')->nullable();
    
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
        Schema::dropIfExists('attendance_jobcard_policies');
    }
}
