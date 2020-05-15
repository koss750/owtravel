<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_logins', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('reference');
            $table->integer('user_id');
            $table->string('bank');
            $table->string('notes');
            $table->string('username');
            $table->mediumText('password1');
            $table->mediumText('password2');
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
        Schema::dropIfExists('bank_logins');
    }
}
