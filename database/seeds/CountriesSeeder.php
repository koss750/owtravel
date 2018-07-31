<?php


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ixudra\Curl\Facades\Curl;

class CountriesSeeder extends Seeder
{
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
                $countries = \App\Country::all();
                if (!$countries->isEmpty()) {
                        echo "Removing existing countries from database. This action cannot be reversed. \r\n";
                        \App\Country::truncate();
                }
                $results = \Illuminate\Support\Facades\Cache::remember('countries', 60, function () {
                        return Curl::to('https://restcountries.eu/rest/v2/all')
                            ->withHeader('X-Requested-With: XMLHttpRequest')
                            ->asJsonResponse()
                            ->returnResponseObject()
                            ->get();
                });
                foreach ($results->content as $country) {

                        $countryObject = new \App\Country;
                        $countryObject->title = $country->name;
                        $countryObject->iso_2 = $country->alpha2Code;
                        $countryObject->iso_3 = $country->alpha3Code;
                        $countryObject->iso_numeric = $country->numericCode;


                        $countryObject->save();
                }
        }
}
