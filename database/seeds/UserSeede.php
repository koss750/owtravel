<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Testing\Fakes;

class UserSeede extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        foreach (range(1, 20) as $index) {

            $user = new \App\User;
            $user->email = $faker->email;
            $user->name = $faker->name;
            $user->password = bcrypt('secret');
            $user->save();

        }
    }   }
