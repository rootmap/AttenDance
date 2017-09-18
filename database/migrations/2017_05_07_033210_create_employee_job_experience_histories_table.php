<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeJobExperienceHistoriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
     
    public function up() {
        Schema::create('employee_job_experience_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('emp_code')->nullable();
            $table->text('company_address')->nullable();
            $table->text('desigantion')->nullable();
            $table->text('responsibility')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('cirtificateupload')->nullable();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('employee_job_experience_histories');
    }

}
