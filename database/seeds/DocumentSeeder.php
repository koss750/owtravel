<?php

use App\DocumentTypes;
use App\User;
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

        foreach (range(1, 100) as $index) {

            $userId=User::all()->random()->id;
            $reference = $userId . $faker->randomNumber(5);

            DB::table('documents')->insert([
                'user_id' => $userId,
                'reference' => $reference,
                'document_type_id' => DocumentTypes::all()->random()->id,
                'document_link_type_id' => $faker->randomNumber(1),
                'link' => $faker->address,
                'description' => $faker->bankAccountNumber
            ]);
        }
    }   }
