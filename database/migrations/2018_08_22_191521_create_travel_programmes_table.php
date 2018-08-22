<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTravelProgrammesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_programmes', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('reference');
            $table->string('name');
            $table->string('start_date');
            $table->string('end_date');
            $table->string('main_destination');
            $table->string('countries');
            $table->string('human_reference');
            $table->integer('coolness_factor');
            $table->integer('created_by');
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
        Schema::dropIfExists('travel_programmes');
    }
}
