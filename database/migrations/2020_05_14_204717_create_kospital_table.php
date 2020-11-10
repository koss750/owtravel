<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKospitalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kospital', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('reference');
            $table->string('code');
            $table->string('name');
            $table->integer('default_doze');
            $table->string('dd_units');
            $table->string('default_origin');
            $table->boolean('otc');
            $table->boolean('cd');
            $table->boolean('supplement');
            $table->boolean('pom');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kospital');
    }
}
