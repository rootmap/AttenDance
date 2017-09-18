<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('user_id');
            $table->string('emp_code');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->text('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->date('join_date');
            $table->text('image')->nullable();
            $table->unsignedInteger('blood_group')->nullable();
            $table->unsignedInteger('marital_status')->nullable();
            $table->unsignedInteger('gender')->nullable();
            $table->unsignedInteger('country')->nullable();
            $table->unsignedInteger('city')->nullable();
            $table->boolean('is_ot_eligible')->nullable();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('employee_infos');
    }
}
