<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeavePoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('leave_title');
            $table->string('leave_short_code');
            $table->string('total_days');
            $table->boolean('is_applicable_for_all');
            $table->boolean('is_leave_cut_applicable');
            $table->boolean('is_carry_forward');
            $table->boolean('is_document_upload');
            $table->boolean('is_holiday_deduct');
            $table->unsignedInteger('document_upload_after_days');
            $table->unsignedInteger('max_carry_forward_days');
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
        Schema::dropIfExists('leave_policies');
    }
}
