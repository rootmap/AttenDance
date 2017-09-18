<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyWorkWeekendDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_work_weekend_days', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('saturday');
            $table->unsignedInteger('sunday');
            $table->unsignedInteger('monday');
            $table->unsignedInteger('tuesday');
            $table->unsignedInteger('wednesday');
            $table->unsignedInteger('thursday');
            $table->unsignedInteger('friday');
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
        Schema::dropIfExists('company_work_weekend_days');
    }
}
