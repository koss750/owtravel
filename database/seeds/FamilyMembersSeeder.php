<?php

use App\FamilyMember;
use Illuminate\Database\Seeder;

class FamilyMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
        public function run()
        {
                $faker = Faker\Factory::create();
                FamilyMember::truncate();

                $types = ["spouse", "child",    "parent",   "grandparent",  "side relative", "grandchild"];
        $inverse_types = ["spouse", "parent",   "child",    "grandchild",   "side relative", "grandparent"];
                if (sizeof($types)!=sizeof($inverse_types)) abort(500, "Inverses not matched");
                $length = sizeof($types)-1;


                foreach (range(1, $length) as $index) {
                        $member = new FamilyMember();
                        $member->description = $types[$index];
                        $member->inverse_of = $inverse_types[$index];
                        $member->save();
                }
        }
}
