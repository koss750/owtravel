<?php

namespace App\Http\Controllers;

use App\Link;
use App\LinkHook;
use App\LinkLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class LinkHookController extends Controller
{

    public $action;
    public $hook;
    public $code;
    public $debug;
    public $curfew;
    public $client;
    public $lineOne;
    public $lineTwo;
    public $lineSpare;
    public $spareVariable;


    public function index($action, $hook) {

        if (isset($action)) {

            $this->action = $action;
            switch ($action) {
                case "debug":
                    $this->debug = true;
                    break;
                case "notification":
                    break;
                case "text":
                    break;
                case "voip":
                    break;
                default:
                    abort(404, "Action undefined");
            }

        }

        if (isset($hook)) {

            $this->hook = $hook;
            switch ($hook) {
                case "lc":
                    if (!$this->debug) $this->dieOfCurfew(['6', '11'], ['Sat', 'Sun']);
                    $this->lizzieMorningCommute();
                    return $this->sendToIffft("L");
                    break;
                case "kc":
                    if (!$this->debug) $this->dieOfCurfew(['6', '10'], ['Sat', 'Sun']);
                    $this->kossMorningCommute();
                    return $this->sendToIffft("K");
                    break;
                case "off_bed":
                    if (!$this->debug) $this->dieOfCurfew(['6', '9'], ['Sat', 'Sun']);
                    Artisan::call('link:check:kc');
                    break;
                case "kaec":
                    if (!$this->debug) $this->dieOfCurfew(['13', '19'], ['Sat', 'Sun']);
                    return $this->kossEveningCommuteAdvanceNotice();
                    break;
                case "kiec":
                    if (!$this->debug) $this->dieOfCurfew(['13', '20'], ['Sat', 'Sun']);
                    return $this->kossEveningCommuteAdvanceNotice(0);
                    break;
                case "k_ebbs_pm":
                    if (!$this->debug) $this->dieOfCurfew(['14', '23']);
                    return $this->arrivedToEbbsfleetPM();
                    break;
                case "k_ebbs_am":
                    if (!$this->debug) $this->dieOfCurfew(['7', '14']);
                    return $this->arrivedToEbbsfleetAM();
                    break;
            }

        }

    }

    public function dieOfCurfew($time, $days = [], $actions = [])
    {
        if (!$this->debug) {
            $timeNow = date("H");
            $dayNow = date("D");

            if ($timeNow < $time[0] || $timeNow > $time[1]) {
                abort(425, "$timeNow:00 outside working hours");
            }

            foreach ($days as $day) {
                if ($day == $dayNow) {
                    abort(425, "$day outside working days");
                }
            }

            foreach ($actions as $action) {
                if ($action == $this->action) {
                    abort(400, "$this->hook incompatable with $this->action");
                }
            }
        }
    }

    public function waterlooEast()
    {

        try {
            $drivingTimes = $this->googleDrivingTime("marden+station", "51.231953,0.504038");
            $drivingTime = $drivingTimes[1];
            $trafficRatio = $drivingTimes[2];
            $drivingCondition = $this->trafficCondition($trafficRatio);
            $walkingTime = 5;
        } catch (\Exception $e) {
            var_dump ($e);
            abort('500', "Error getting information from Google");
        }

        try {
            $departingStn = "LBG";
            $arrivalStn = "MRN";
            $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn, $drivingTime);
            $times = $this->nationalRailSpecificTrain($mainResponse->departures->all[0]->service_timetable->id, $departingStn, $arrivalStn);
        } catch (\Exception $e) {
            abort('500', "Error getting information from Darwin");
        }

        $timeMarden = $times[0];
        $timeAfterMardenInMinutes = $drivingTime + $walkingTime;
        $timeHome = date('H:i', strtotime("$timeMarden + $timeAfterMardenInMinutes minutes"));
        $statusTrain = $times[2];

        $params = [
            "API_ACTION" => $this->action,
            "API_VAR1" => "Good evening! ETA $timeHome",
            "API_VAR2" => "Koss is en route home and is now around Waterloo East. Train is $statusTrain due to arrive to Marden at $timeMarden. Traffic home is $drivingCondition. Have a wonderful evening."
        ];

        try {
            $hook = $this->hookUp('IFTTT', $params);
            return $hook->fullResponse;
        } catch (\Exception $e) {
            abort('500', "Error passing information to IFTTT");
        }

    }

    public function googleDrivingTime($from, $to)
    {
        $routeOptimization = false;

        if ($from=="home") {
            $from = "2+Cricketers+way+coxheath";
            $routeOptimization = true;
        }

        if ($to=="home") {
            $to = "2+Cricketers+way+coxheath";
        }

        $hook = $this->hookUp('GOOGLE_MAPS', ['API_FROM' => $from, 'API_TO' => $to], $this->debug);
        $response = json_decode($hook->fullResponse);

        $duration = $response->routes[0]->legs[0]->duration->value;
        $duration_in_traffic = $response->routes[0]->legs[0]->duration_in_traffic->value;

        if (isset ($response->routes[0]->legs[1]->duration->value)) {
            $duration = $duration + $response->routes[0]->legs[1]->duration->value;
            $duration_in_traffic = $duration_in_traffic + $response->routes[0]->legs[1]->duration_in_traffic->value;
        }

        $ratio = (($duration_in_traffic - $duration) / $duration) * 100;
        $in_minutes = $duration / 60;
        $in_minutes_in_traffic = $duration_in_traffic / 60;
        $round_mins = round($in_minutes, 0);
        $round_mins_in_traffic = round($in_minutes_in_traffic, 0);
        $round_ratio = round($ratio);

        if ($routeOptimization) {
            $turn = $response->routes[0]->legs[0]->steps[2]->maneuver;
        }

        $logValue = [
            "from" => $from,
            "to" => $to,
            "date" => Carbon::today()->toDateString(),
            "time" => Carbon::now()->toTimeString(),
            "timestamp" => Carbon::now(),
            "time_in_traffic" => $in_minutes_in_traffic
        ];
        $this->log("historic-information", "google-maps-request", json_encode($logValue));

        return [$round_mins, $round_mins_in_traffic, $round_ratio, ($turn ?? null)];
    }

    private function trafficCondition($ratio)
    {

        if ($ratio > 120) $drivingCondition = "f*cked up! or Google is being weird.";
        else if ($ratio > 90) $drivingCondition = "a total disaster!";
        else if ($ratio > 50) $drivingCondition = "bad!";
        else if ($ratio > 20) $drivingCondition = "congested";
        else if ($ratio > 10) $drivingCondition = "slightly busier";
        else if ($ratio < 0) $drivingCondition = "particularly good";
        else $drivingCondition = "ok";

        return $drivingCondition;
    }

    public function nationalRailStationLive($departingStn, $arrivalStn, $offset)
    {
        if ($offset>120) {
            $aboveHr = $offset-120;
            if ($aboveHr<10) {
                $offset="02:0$aboveHr";
            }
            else {
                $offset = "02:$aboveHr";
            }
        }
        else if ($offset>60) {
            $aboveHr = $offset-60;
            if ($aboveHr<10) {
                $offset="01:0$aboveHr";
            }
            else {
                $offset = "01:$aboveHr";
            }
        }
        else {
            if ($offset<10) {
                $offset="00:0$offset";
            }
            else {
                $offset = "00:$offset";
            }
        }
        $hook = $this->hookUp('NATIONAL_RAIL', ['API_FROM' => $departingStn, 'API_TO' => $arrivalStn, 'API_OFFSET' => $offset], $this->debug);
        return $hook->fullResponse;
    }

    protected function hookUp($type, $params, $debug = false)
    {
        return new LinkHook($type, $params, $debug);
    }

    private function nationalRailSpecificTrain($service, $departingStn, $arrivalStn)
    {

        $hook = $this->hookUp("BASIC", ['API_URL' => $service], $this->debug);
        $data = $hook->objectResponse;

        $result = [];
        if (isset($data->stops)) {
            foreach ($data->stops as $stop) {

                if ($stop->station_code == $departingStn) {

                    $result["status"] = $this->trainStatus($stop->status);

                    if (!empty($stop->expected_departure_time)) $result["departure_time"] = $stop->expected_departure_time;
                    else if (!empty($stop->aimed_departure_time)) $result["departure_time"] = $stop->aimed_departure_time;
                    else $result["departure_time"] = "UNKNOWN";

                    $result["platform"] = $stop->platform;
                }

                if ($stop->station_code == $arrivalStn) {
                    if (!empty($stop->expected_arrival_time)) $result["arrival_time"] = $stop->expected_arrival_time;
                    else if (!empty($stop->aimed_arrival_time)) $result["arrival_time"] = $stop->aimed_arrival_time;
                    else $result["arrival_time"] = "UNKNOWN";

                    $result['arrival_time'] = date('H:i', strtotime("$result[arrival_time]"));
                }
            }
        }

        else $result = 0;


        return $result;

    }

    private function trainStatus($status)
    {
        switch ($status) {
            case "STARTS HERE":
                return "scheduled";
            case "LATE":
                return "delayed :/";
            case "ON TIME":
                return "on time";
            case "EARLY":
                return "on time";
            case ":":
                return "unspecified";
            case NULL:
                return "unspecified";
            default:
                return $status;
        }
    }

    /**
     * @return array
     */
    public function active()
    {
        echo "Link system available to use";
    }

    public function wakeUp()
    {
        try {

            $drivingTimes = $this->googleDrivingTime("home", "ebbsfleet+station");
            $drivingTime = $drivingTimes[1];
            $trafficRatio = $drivingTimes[2];
            $drivingCondition = $this->trafficCondition($trafficRatio);

        } catch (\Exception $e) {
            abort('500', $e);
        }


        $walkingTime = 5;
        $offsetTime = $drivingTime+$walkingTime;

        try {
            $departingStn = "EBD";
            $arrivalStn = "SFA";
            $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn, $offsetTime);
            $mainResponse = json_decode($mainResponse);
            $times = $this->nationalRailSpecificTrain($mainResponse->departures->all[0]->service_timetable->id, $departingStn, $arrivalStn);
        } catch (\Exception $e) {
            if ($this->debug) echo $e;
            abort('500', "Error getting information from Darwin");

        }

        $timeMarden = $times[0];
        $timeAfterMardenInMinutes = $drivingTime + $walkingTime;
        $timeHome = date('H:i', strtotime("$timeMarden + $timeAfterMardenInMinutes minutes"));
        $statusTrain = $times[2];

        $this->lineOne = "Good evening! ETA $timeHome";
        $this->lineTwo = "Koss is en route home and has just left St Pancras. Train is $statusTrain due to arrive to Ebbsfleet at $timeMarden. Traffic home is $drivingCondition, ETA $timeHome. Have a wonderful evening.";

        $this->sendToIffft("K");

    }

    public function lizzieMorningCommute()
    {
        //fremlin+walk+car+park&waypoints=Dean+street+maidstone
        $drivingTimes = $this->compareDrivingTimes("home", "fremlin+walk+car+park", "&waypoints=via:Dean+street+maidstone", "&waypoints=via:loose+road+maidstone");

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];

        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 11;

        $commuteTime = $walkingTime+$drivingTime;
        $timeNow = now();
        $arrivalTime = date('H:i', strtotime("$timeNow + $commuteTime minutes"));


        $slowerBy = ($drivingTimes["alternative"]-$drivingTime);

        $directions = $this->processHeathRoadTurn($drivingTimes[3], $slowerBy);

        $this->lineOne = "Good morning! Roads are $drivingCondition.";
        $this->lineTwo = "It will take you $drivingTime minutes to get to Fremlin walk. $directions";
    }

    public function kossMorningCommute($offset = 12)
    {

        $drivingTimes = $this->compareDrivingTimes("home", "ebbsfleet+international", "&waypoints=via:Dean+street+maidstone", "&waypoints=via:loose+road+maidstone");

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];

        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 6;

        $commuteTime = $walkingTime+$drivingTime;
        $timeNow = now();
        $arrivalTime = date('H:i', strtotime("$timeNow + $commuteTime minutes + $offset minutes"));

        $slowerBy = ($drivingTimes["alternative"]-$drivingTime);
        $directions = $this->processHeathRoadTurn($drivingTimes[3], $slowerBy);

        $departingStn = "EBD";
        $arrivalStn = "SPX";
        $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn, ($drivingTime+$walkingTime+$offset));
        $mainResponse = json_decode($mainResponse);
        $nextTrainFromPlatform = $this->nationalRailNextFromPlatform($mainResponse, [5, 2]);

        if ($nextTrainFromPlatform) $times = $this->nationalRailSpecificTrain($nextTrainFromPlatform, $departingStn, $arrivalStn);
        else $times = 0;

        if ($times == 0) {  //No trains returned by Specific Train hook
            $this->lineOne = "Hello. Roads are $drivingCondition.";
            $this->lineTwo = "It will take you $drivingTime minutes to get to Ebbsfleet and if you leave in $offset minutes you'll get there at $arrivalTime. However, no trains will be leaving $departingStn then.";
        }
        else {
            $trainDeparture = $times["departure_time"];
            $platform = $times["platform"];
            $atWork = date('H:i', strtotime("$trainDeparture + 36 minutes"));
            $this->spareVariable = $atWork;
            $this->lineOne = "Good morning. Roads are $drivingCondition.";
            $this->lineTwo = "It will take you $drivingTime minutes to get to Ebbsfleet. If you leave in 12 minutes, you should be on platform $platform at $arrivalTime and in time for $trainDeparture train. $directions This places you at work at around $atWork";
        }
    }

    public function arrivedToEbbsfleetAM() {

        $departingStn = "EBD";
        $arrivalStn = "SPX";
        $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn, (6));
        $mainResponse = json_decode($mainResponse);
        $nextTrainFromPlatform = $this->nationalRailNextFromPlatform($mainResponse, [5, 2]);

        if ($nextTrainFromPlatform) $times = $this->nationalRailSpecificTrain($nextTrainFromPlatform, $departingStn, $arrivalStn);
        else $times = 0;

        if ($times == 0) {  //No trains returned by Specific Train hook
            $this->lineOne = "Welcome to Ebbsfleet, Creator.";
            $this->lineTwo = "No trains are departing it seems, sorry if it's true..";
        }
        else {
            $trainDeparture = $times["departure_time"];
            $platform = $times["platform"];
            $atWork = date('H:i', strtotime("$trainDeparture + 36 minutes"));
            $this->spareVariable = $atWork;
            $this->lineOne = "Good morning and welcome to Ebbsfleet";
            $this->lineTwo = "You should make the $trainDeparture train on platform $platform. Work ETA - $atWork";
        }
        $this->sendToIffft("K");
    }

    public function arrivedToEbbsfleetPM() {

        $drivingTimes = $this->googleDrivingTime( "ebbsfleet+international", "home");

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];

        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 11;

        $commuteTime = $walkingTime+$drivingTime;
        $timeNow = now();
        $arrivalTime = date('H:i', strtotime("$timeNow + $commuteTime minutes"));

        $this->lineOne = "Welcome to Ebbsfleet! Home ETA $arrivalTime";
        $this->lineTwo = "Roads are $drivingCondition. It will take you $drivingTime minutes to get home with ETA at $arrivalTime.";
        $this->sendToIffft("K");

        $this->lineOne = "ETA $arrivalTime";
        $this->lineTwo = "K arrived to Ebbsfleet. His updated ETA is $arrivalTime";
        $this->sendToIffft("L");

    }

    public function goingToLondonViaEbbsfleet()
    {

        $drivingTimes = $this->compareDrivingTimes("home", "ebbsfleet+international", "&waypoints=via:Dean+street+maidstone", "&waypoints=via:loose+road+maidstone");

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];

        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 6;

        $commuteTime = $walkingTime+$drivingTime;
        $timeNow = now();
        $arrivalTime = date('H:i', strtotime("$timeNow + $commuteTime minutes + 5 minutes"));

        $slowerBy = ($drivingTimes["alternative"]-$drivingTime);
        $directions = $this->processHeathRoadTurn($drivingTimes[3], $slowerBy);

        $departingStn = "EBD";
        $arrivalStn = "SPX";
        $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn, ($drivingTime+$walkingTime+5));
        $mainResponse = json_decode($mainResponse);
        $nextTrainFromPlatform = $this->nationalRailNextFromPlatform($mainResponse, [5, 2]);

        if ($nextTrainFromPlatform) $times = $this->nationalRailSpecificTrain($nextTrainFromPlatform, $departingStn, $arrivalStn);
        else $times = 0;

        if ($times == 0) {  //No trains returned by Specific Train hook
            $this->lineOne = "Hello. Roads are $drivingCondition.";
            $this->lineTwo = "It will take you $drivingTime minutes to get to Ebbsfleet and if you leave in 5 minutes you'll get there at $arrivalTime. However, no trains will be leaving $departingStn then.";
        }
        else {
            $trainDeparture = $times["departure_time"];
            $platform = $times["platform"];
            $arrivalTime = date('H:i', strtotime("$trainDeparture + 19 minutes"));
            $this->lineOne = "Good morning. Roads are $drivingCondition.";
            $this->lineTwo = "It will take you $drivingTime minutes to get to Ebbsfleet. If you leave in 5 minutes, you should be on platform $platform at $arrivalTime and in time for $trainDeparture train. $directions This places you at St.P at around $arrivalTime";
        }
    }

    public function kossAlternativeCommute($offset = 12) {

        $drivingTimes = $this->googleDrivingTime("home", "marden+station");
        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];
        $walkingTime = 2;

        $commuteTime = $walkingTime+$drivingTime;
        $timeNow = now();
        $arrivalTime = date('H:i', strtotime("$timeNow + $commuteTime minutes + $offset minutes"));

        $departingStn = "MRN";
        $arrivalStn = "CHX";
        $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn, ($commuteTime+$offset));
        $mainResponse = json_decode($mainResponse);
        $nextTrainFromPlatform = $this->nationalRailNextFromPlatform($mainResponse, [null, 1, 2]);

        $times = $this->nationalRailSpecificTrain($nextTrainFromPlatform, $departingStn, $arrivalStn);

        $trainDeparture = $times["departure_time"];
        $trainArrival = $times["arrival_time"];
        $atWork = date('H:i', strtotime("$trainArrival + 16 minutes"));
        $this->spareVariable = $atWork;
        $this->lineSpare = "It will take you $drivingTime minutes to get to Marden. You should be in time for $trainDeparture train, work ETA $atWork";
    }

    public function kossCompareCommutes($offset = 12) {
        $this->kossMorningCommute($offset);
        $defaultArrival = $this->spareVariable;
        $this->kossAlternativeCommute($offset);
        $alternativeArrival = $this->spareVariable;
        $this->spareVariable = [$defaultArrival, $alternativeArrival];
    }

    public function processKossCommuteTimeDifference() {

        $first = Carbon::parse($this->spareVariable[0]);
        $second = Carbon::parse($this->spareVariable[1]);
        $difference = $first->diffInMinutes($second);
        if ($first->greaterThan($second)) return -$difference;
        else return $difference;

    }


    public function kossEveningCommuteAdvanceNotice($walkingTime = 27)
    {

        $drivingTimes = $this->googleDrivingTime( "ebbsfleet+international", "home");

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];

        $drivingCondition = $this->trafficCondition($trafficRatio);
        $departingStn = "SPX";
        $arrivalStn = "EBD";
        $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn, ($walkingTime));
        $mainResponse = json_decode($mainResponse);
        $nextTrainFromPlatform = $this->nationalRailNextFromPlatform($mainResponse, [11, 12, 13]);
        if ($nextTrainFromPlatform) $times = $this->nationalRailSpecificTrain($nextTrainFromPlatform, $departingStn, $arrivalStn);
        else $times = 0;

        if ($times == 0) {  //No trains returned by Specific Train hook
            $this->lineOne = "Hello creator";
            $this->lineTwo = "Weird shit is happening with the trains, none seem to be operating. Sorry if it's true";
        }
        else {
            $trainDeparture = $times["departure_time"];
            $trainArrival = $times["arrival_time"];
            $platform = $times["platform"];
            $atHome = date('H:i', strtotime("$trainArrival + $drivingTime minutes"));
            if ($walkingTime>0) {
                $this->lineOne = "Good evening creator.";
                $this->lineTwo = "You should make the $trainDeparture train (platform $platform). Roads are $drivingCondition, will take $drivingTime min to drive home, getting you there at $atHome";
                $this->sendToIffft("K");
            }
            else {
                $this->lineOne = "Good evening!";
                $this->lineTwo = "K is about to leave and should get home by $atHome";
                $this->sendToIffft("L");
            }
        }
    }

    private function nationalRailNextFromPlatform($darwinResponse, $platforms) {
        foreach ($darwinResponse->departures->all as $item) {
            if (in_array($item->platform, $platforms)) return $item->service_timetable->id;
        }
    }

    public function processHeathRoadTurn($turn, $alternative) {

        $logValue = [
            "turn" => $turn,
            "faster_by" => $alternative,
            "date" => Carbon::today()->toDateString()
        ];
        $this->log("historic-information", "coxheath-maidstone-route", json_encode($logValue));

        if ($turn == "turn-right") {
            if ($alternative < 5) {
                return "Loose Road will only delay you $alternative min";
            }
            return "Loose Road will delay you by $alternative min";
        }
        else return "Dean Street will delay you by $alternative min";

    }

    public function compareDrivingTimes($from, $to, $via1, $via2) {
        $firstRoute = $to . $via2;
        $drivingTimes1 = $this->googleDrivingTime($from, $firstRoute);
        $drivingTime1 = $drivingTimes1[1];

        $secondRoute = $to . $via1;
        $drivingTimes2 = $this->googleDrivingTime($from, $secondRoute);
        $drivingTime2 = $drivingTimes2[1];

        if ($drivingTime1<=$drivingTime2) {
            $drivingTimes1["alternative"] = $drivingTime2;
            return $drivingTimes1;
        }
        else {
            $drivingTimes2["alternative"] = $drivingTime1;
            return $drivingTimes2;
        }
    }

    public function weatherMaidstone()
    {

    }

    public function endOfWeek() {
        $week = (int)today()->format('W');
        $year = today()->format('Y');
        $this->lineOne = "Happy Friday!";
        $this->lineTwo = "It's the end of week $week of $year. Have a good weekend!";
    }

    public function eveningWeather() {
        $date = today()->addDay(1)->toDateString();
        $dayToday = date("D");

        if ($dayToday == "Fri" || $dayToday == "Sat") {
            $targetTime = "05:30";
            $targetHourString = "5.30am";
        }
        else {
            $targetTime = "05:30";
            $targetHourString = "5.30am";
        }

        $carbon_obj = Carbon::createFromFormat('Y-m-d H:i:s' , $date . $targetTime . ':00','Europe/London');

        $timestamp = $carbon_obj->timestamp;

        $hook = $this->hookUp("DARK_SKY", [
            'API_TIME' => $timestamp
        ], $this->debug
        );

        $response = $hook->objectResponse->currently;
        $dailyData = $hook->objectResponse->daily->data[0];
        $summary = $response->summary;
        $temperature = round(($response->temperature - 32) / 1.8);
        $rainChance = $response->precipProbability*100;
        $rainPower = $response->precipIntensity*10000;
        $sunrise = Carbon::createFromTimestamp($dailyData->sunriseTime)->format('g:i');    
        $maxTemp = round(($dailyData->temperatureHigh - 32) / 1.8);   
        $maxTempTime = Carbon::createFromTimestamp($dailyData->temperatureHigh)->format('g:i');

        $this->lineOne = "Good evening! Weather report:";

        if ($rainPower == 0 && $rainChance ==0) {
            $this->lineTwo = "At $targetHourString $temperature" . "°C, no rain is expected . Sunrise $sunrise. Max temp " . $maxTemp. "°C at $maxTempTime" . "pm";
        }
        else {
            $worstRain = Carbon::createFromTimestamp($dailyData->precipIntensityMaxTime)->format('g:i A');
            $this->lineTwo = "At $targetHourString, $summary, $temperature" . "°C. $rainChance% rain. Rain intensity $rainPower. Worst rain at $worstRain. Sunrise $sunrise. Max temp " . $maxTemp. "°C at $maxTempTime" . "pm";
        }

        $logValue = [
            "temperature" => $temperature,
            "rain_chance" => $rainChance,
            "rain_intensity" => $rainPower,
            "date" => Carbon::today()->toDateString()
        ];
        $this->log("historic-information", "morning_weather_forecast", json_encode($logValue));



    }

    public function oneHourWeather($startingTime) {
        $date = $startingTime->addHour(1)->startOfHour()->toDateTimeString();
        $carbon_obj = Carbon::createFromFormat('Y-m-d H:i:s' , $date,'Europe/London');
        $timestamp = $carbon_obj->timestamp;

        $hook = $this->hookUp("DARK_SKY", [
            'API_TIME' => $timestamp
        ], $this->debug
        );

        $response = $hook->objectResponse->currently;
        $rainChanceLater = $response->precipProbability;
        $rainPowerLater = $response->precipIntensity;

        $date = Carbon::now()->toDateTimeString();
        $carbon_obj = Carbon::createFromFormat('Y-m-d H:i:s' , $date,'Europe/London');
        $timestamp = $carbon_obj->timestamp;

        $hook = $this->hookUp("DARK_SKY", [
            'API_TIME' => $timestamp
        ], $this->debug
        );

        $response = $hook->objectResponse->currently;
        $rainChanceNow = $response->precipProbability;
        $rainPowerNow = $response->precipIntensity;



        $this->lineOne = [
            "intensityNow" => $rainPowerNow*10000,
            "chanceNow" => $rainChanceNow*100,
            "chanceLater" => $rainChanceLater*100,
            "intensityLater" => $rainPowerLater*10000
        ];

    }

    private function get_weather_in_maidstone()
    {

        // fetch Aeris API output as a string and decode into an object
        $hook = $this->hookUp ("W", ['city' => 'maidstone, uk']);
        $weather = $hook->objectResponse;

        if ($weather->success == true) {

            $degrees = $weather->response->ob->tempC;
            $feels = $weather->response->ob->feelslikeC;
            $description = $weather->response->ob->weather;

            return "The weather in Maidstone is $description. $degrees*C (feels like $feels*C)";

        } else return "There was an error getting the weather";

    }

    public function sendToIffft($recipient) {

        $api = "IFTTT_" . $recipient;

        $toProceed = true;

        if ($this->debug) $toProceed = false;
        if (Cache::pull($api)) $toProceed = false;

        if (!$toProceed) {
            $response = "<br>The following was sent to IFTTT less than 5 mins ago:<br>";
            $response .= "<br> $this->lineOne <br> $this->lineTwo";
            echo $response;
        }
        else {
            try {

                $expiresAt = now()->addMinutes(4);
                Cache::put($this->action . $api, true, $expiresAt);

                $hook = $this->hookUp($api, [
                    'API_VAR1' => $this->lineOne,
                    'API_VAR2' => $this->lineTwo,
                    'API_ACTION' => $this->action
                ], $this->debug
                );

                $logValue = $this->lineOne . " " . $this->lineTwo;

                $this->log($api, $this->action, $logValue);

            } catch (\Exception $e) {

                Cache::forget($api);
                if ($this->debug) report($e);
                abort('500', "Error passing information to IFTTT $e");

            }
        }
    }

    public function log($type, $subtype, $value) {
        $log = new LinkLog;
        $log->type = $type;
        $log->subtype = $subtype;
        $log->value = $value;
        $log->save();
    }

    public function checkWeekendRegimeToday() {
        $date = Carbon::now();
        $timeString = $date->format('dmy');
        return $this->checkRoutineInformation('weekend-regime', $timeString);
    }

    public function checkRoutineInformation($subtype, $value) {
        return LinkLog::where('type', 'routine-information')->where('subtype', $subtype)->where('value', $value)->exists();
    }
}
