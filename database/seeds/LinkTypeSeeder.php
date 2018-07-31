<?php

use App\DocumentTypes;
use App\LinkType;
use Faker\Provider\Uuid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Testing\Fakes;

class LinkTypeSeeder extends Seeder
{
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
                LinkType::truncate();
                $externalTypes = ["general external"];
                $internalTypes = [
                        "scan",
                        "photo",
                        "gen-pop"
                ];

                foreach ($externalTypes as $externalType) {
                        $type = new LinkType;
                        $type->reference = Uuid::uuid();
                        $type->prefix = "http://";
                        $type->name = $externalType;
                        $type->location = 'ext';
                        $type->save();
                }
                foreach ($internalTypes as $internalType) {
                        $type = new LinkType;
                        $type->reference = Uuid::uuid();
                        $type->prefix = $internalType;
                        $type->name = $internalType;
                        $type->location = 'int';
                        $type->save();
                }
        }
}
