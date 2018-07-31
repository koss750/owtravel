<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLinkTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('link_types', function (Blueprint $table) {
            $table->string('location')->after('prefix');
            $table->string('name')->after('reference');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('link_types', function (Blueprint $table) {
                $table->dropColumn('location');
                $table->dropColumn('name');
        });
    }
}
