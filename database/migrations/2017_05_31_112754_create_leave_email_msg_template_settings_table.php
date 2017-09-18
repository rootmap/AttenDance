<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveEmailMsgTemplateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_email_msg_template_settings', function (Blueprint $table) {
          $table->increments('id');
          $table->unsignedInteger('template_type_id')->nullable();
          $table->unsignedInteger('company_id')->nullable();
          $table->text('msg_template')->nullable();
          $table->foreign('template_type_id')->references('id')->on('leave_email_template_types');
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
        Schema::dropIfExists('leave_email_msg_template_settings');
    }
}
