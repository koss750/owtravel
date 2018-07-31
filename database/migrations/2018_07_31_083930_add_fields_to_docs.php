<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
                $table->string('issue_country');
                $table->string('number');
                $table->string('valid_from')->nullable();
                $table->string('valid_to')->nullable();
                $table->string('allows_days')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
                $table->dropColumn('issue_country');
                $table->dropColumn('number');
                $table->dropColumn('valid_from');
                $table->dropColumn('valid_to');
                $table->dropColumn('allows_days');
        });
    }
}
