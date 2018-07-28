<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Testing\Fakes;

class DocumentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        $types = ["passport", "brp", 'driving license'];

        foreach ($types as $type) {

            DB::table('document_types')->insert([
                'description' => $type,
                'reference' => $faker->randomAscii
            ]);
        }
    }   }
