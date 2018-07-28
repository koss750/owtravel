<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeede::class);
        $this->call(DocumentTypesSeeder::class);
        $this->call(DocumentSeeder::class);
    }
}
