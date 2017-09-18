<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemModulePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_module_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('system_module_id');
            $table->unsignedInteger('system_sub_module_id');
            $table->string('name');
            $table->string('link');
            $table->boolean('is_active');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('system_module_id')->references('id')->on('system_modules');
            $table->foreign('system_sub_module_id')->references('id')->on('system_sub_modules');
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
        Schema::dropIfExists('system_module_pages');
    }
}
