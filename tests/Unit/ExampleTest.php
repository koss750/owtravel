<?php

namespace Tests\Unit;

use App\Http\Controllers\LinkHookController;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    private $controller;

    public function setUp() {
        $this->controller = new LinkHookController;
        parent::setup();
    }


    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    public function testGoogleMaps()
    {
        $drivingTimes = $this->controller->googleDrivingTime( "ebbsfleet+international", "home");
        $this->assertGreaterThan(1, $drivingTimes[0]);
        $this->assertGreaterThan(1, $drivingTimes[1]);
        $this->assertGreaterThan(0, $drivingTimes[1]);
    }

    public function testTrainTimes()
    {
        $departingStn = "LBG";
        $arrivalStn = "MRN";
        $mainResponse = $this->controller->nationalRailStationLive($departingStn, $arrivalStn, 0);
        $delayedResponse = $this->controller->nationalRailStationLive($departingStn, $arrivalStn, 90);
        $objectResponse = json_decode($mainResponse);
        $this->assertIsObject($objectResponse);
        //$this->assertNotEquals($mainResponse->departures->all[0]->service_timetable->id, $delayedResponse->departures->all[0]->service_timetable->id);
    }
}
