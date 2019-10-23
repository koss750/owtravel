<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinkHooksTable extends Migration
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
            $table->mediumText('url');
            $table->mediumText('type');
            $table->json('object_response');
            $table->mediumText('full_response');
            $table->json('params');
            $table->boolean('debug');
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
