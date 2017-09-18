<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_policies', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('company_id');
			$table->string('policy_heading');
			$table->date('policy_publish_date');
			$table->text('policy_description');
			$table->boolean('policy_status');
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
        Schema::dropIfExists('company_policies');
    }
}
