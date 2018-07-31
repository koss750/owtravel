<?php

use App\Country;
use App\DocumentTypes;
use App\User;
use Faker\Provider\Uuid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Testing\Fakes;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        \App\Document::truncate();

        foreach (range(1, 100) as $index) {

            $userId = User::all()->random()->id;
            $doc = new \App\Document;
            $doc->user_id = $userId;
            $doc->reference = Uuid::uuid();
            $doc->document_type_id = DocumentTypes::all()->random()->id;
            $doc->issue_country = Country::all()->random()->iso_3;
            $doc->number = rand('20100000', '50999000');
            $doc->description = $faker->creditCardNumber;
            $doc->document_link_type_id = \App\LinkType::all()->random()->id;
            $doc->link = $faker->domainName;
            $doc->save();


        }
    }
}
