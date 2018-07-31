<?php

use App\Family;
use App\FamilyMember;
use App\User;
use Faker\Provider\Uuid;
use Illuminate\Database\Seeder;

class FamiliesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            Family::truncate();

            foreach (range(1, 30) as $index) {

                    $userId = User::all()->random()->id;
                    $relatesTo = User::all()->random()->id;
                    $family = new Family;
                    $family->user_id = $userId;
                    $family->relates_to = $relatesTo;
                    $family->reference = Uuid::uuid();
                    $memberId = FamilyMember::all()->random()->id;
                    $member = FamilyMember::where('id', $memberId)->firstOrFail();
                    $family->member_id = $memberId;
                    $family->save();
                    $family_inverse = new Family;
                    $inverse_member = FamilyMember::where('inverse_of', $member->description)->firstOrFail();
                    $inverse_member_id = $inverse_member->id;
                    $family_inverse->user_id = $relatesTo;
                    $family_inverse->relates_to = $userId;
                    $family_inverse->member_id = $inverse_member_id;
                    $family_inverse->reference = Uuid::uuid();
                    $family_inverse->save();


            }
    }
}
