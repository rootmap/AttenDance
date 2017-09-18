<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMapRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_map_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('system_module_id')->nullable();
            $table->unsignedInteger('system_sub_module_id')->nullable();
            $table->unsignedInteger('system_module_page_id')->nullable();
            $table->unsignedInteger('system_access_role_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->boolean('create_permission')->nullable();
            $table->boolean('edit_permission')->nullable();
            $table->boolean('view_list_permission')->nullable();
            $table->boolean('delete_permission')->nullable();
            $table->foreign('system_access_role_id')->references('id')->on('system_access_roles');
            $table->foreign('system_module_page_id')->references('id')->on('system_module_pages');
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
        Schema::dropIfExists('user_map_roles');
    }
}
