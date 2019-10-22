<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('link_hooks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->string('type');
            $table->string('object_response');
            $table->string('full_response');
            $table->string('client');
            $table->string('params');
            $table->string('debug');
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
        Schema::dropIfExists('link_hooks');
    }
}
