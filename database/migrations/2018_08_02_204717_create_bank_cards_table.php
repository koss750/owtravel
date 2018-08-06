<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('reference');
            $table->integer('user_id');
            $table->string('bank');
            $table->string('ln')->unique();
            $table->string('expiry_month');
            $table->string('expiry_year');
            $table->string('CVC');
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
        Schema::dropIfExists('bank_cards');
    }
}
