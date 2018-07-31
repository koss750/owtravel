<?php

use App\DocumentTypes;
use Faker\Provider\Uuid;
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
        DocumentTypes::truncate();

        $types = ["passport", "brp", 'driving license', 'visa', 'id photo'];

        foreach ($types as $type) {

            $doc_type = new DocumentTypes;
            $doc_type->description = $type;
            $doc_type->reference = Uuid::uuid();
            $doc_type->save();
        }
    }   }
