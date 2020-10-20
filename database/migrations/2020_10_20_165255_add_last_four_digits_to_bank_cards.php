<?php

use App\BankCard;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastFourDigitsToBankCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_cards', function (Blueprint $table) {
            $table->string('last_four')->after('bank');
            $items = BankCard::all();
            foreach ($items as $item) {
                $long = $item->ln;

                try {
                    $short = substr(decrypt($long), -4);
                } catch (\Exception $e) {
                    $short = substr($long, -4);
                }

                $item->last_four = $short;
                $item->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_cards', function (Blueprint $table) {
            $table->dropColumn('last_four');
        });
    }
}
