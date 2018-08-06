<?php

use App\User;
use Faker\Provider\Uuid;
use Illuminate\Database\Seeder;

class BankingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        \App\BankCard::truncate();

        foreach (range(1, 100) as $index) {

            $userId = User::all()->random()->id;
            $doc = new \App\BankCard();
            $doc->user_id = $userId;
            $doc->reference = Uuid::uuid();
            $doc->bank = $faker->company;
            $doc->ln = $faker->creditCardNumber;
            $doc->expiry_month = rand(1, 12);
            $doc->expiry_year = rand(19, 24);
            $doc->cvc = rand(100, 999);
            $doc->save();


        }
    }
}
