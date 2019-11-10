<?php

namespace App\Http\Controllers;

use App\LinkHook;
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
                default:
                    abort(404, "Action undefined");
            }

        }

        if (isset($hook)) {

            $this->hook = $hook;

            switch ($hook) {
                case "we":
                //    if (!$this->debug) $this->dieOfCurfew(['15', '21'], ['Sat', 'Sun']);
                    return $this->waterlooEast();
                    break;
                case "pw":
                //    if (!$this->debug) $this->dieOfCurfew(['16', '22'], ['Sat', 'Sun']);
                    return $this->paddockWood();
                    break;
                case "wu":
                //    if (!$this->debug) $this->dieOfCurfew(['6', '23'], ['Sat', 'Sun'], ['text']);
                    return $this->wakeUp();
                    break;
                case "lc":
                //    if (!$this->debug) $this->dieOfCurfew(['5', '23'], ['Sat', 'Sun']);
                    $this->lizzieMorningCommute();
                    return $this->sendToIffft("L");
                    break;
                case "kc":
                //    if (!$this->debug) $this->dieOfCurfew(['5', '23'], ['Sat', 'Sun']);
                    $this->kossMorningCommute();
                    return $this->sendToIffft("K");
                    break;
                case "kw":
                    //    if (!$this->debug) $this->dieOfCurfew(['5', '23'], ['Sat', 'Sun']);
                    return $this->kossEveningCommuteAdvanceNotice();
                    break;
            }

        }

    }

    public function dieOfCurfew($time, $days, $actions = [])
    {
        if (!$this->debug) {
            $timeNow = date("H");
            $dayNow = date("D");

            if ($timeNow < $time[0] || $timeNow > $time[1]) {
                abort(403, "$timeNow:00 outside working hours");
            }

            foreach ($days as $day) {
                if ($day == $dayNow) {
                    abort(403, "$day outside working days");
                }
            }

            foreach ($actions as $action) {
                if ($action == $this->action) {
                    abort(403, "$this->hook incompatable with $this->action");
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

    public function paddockWood()
    {

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

        $directions = $this->processHeathRoadTurn($drivingTimes[3], $drivingTimes["alternative"]);

        $this->lineOne = "Good morning. Roads are $drivingCondition.";
        $this->lineTwo = "It will take you $drivingTime minutes to get to Fremlin walk. If you leave now, you should be at KCC at $arrivalTime. $directions. ";
    }

    public function kossMorningCommute()
    {

        $drivingTimes = $this->compareDrivingTimes("home", "ebbsfleet+international", "&waypoints=via:Dean+street+maidstone", "&waypoints=via:loose+road+maidstone");

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];

        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 6;

        $commuteTime = $walkingTime+$drivingTime;
        $timeNow = now();
        $arrivalTime = date('H:i', strtotime("$timeNow + $commuteTime minutes + 20 minutes"));

        $directions = $this->processHeathRoadTurn($drivingTimes[3], $drivingTimes["alternative"]);

        $departingStn = "EBD";
        $arrivalStn = "SPX";
        $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn, ($drivingTime+$walkingTime+20));
        $mainResponse = json_decode($mainResponse);
        $nextTrainFromPlatform = $this->nationalRailNextFromPlatform($mainResponse, [5, 2]);

        if ($nextTrainFromPlatform) $times = $this->nationalRailSpecificTrain($nextTrainFromPlatform, $departingStn, $arrivalStn);
        else $times = 0;

        if ($times == 0) {  //No trains returned by Specific Train hook
            $this->lineOne = "Hello. Roads are $drivingCondition.";
            $this->lineTwo = "It will take you $drivingTime minutes to get to Ebbsfleet and if you leave in 20 minutes you'll get there at $arrivalTime. However, no trains will be leaving $departingStn then.";
        }
        else {
            $trainDeparture = $times["departure_time"];
            $platform = $times["platform"];
            $atWork = date('H:i', strtotime("$trainDeparture + 32 minutes"));
            $this->lineOne = "Good morning. Roads are $drivingCondition.";
            $this->lineTwo = "It will take you $drivingTime minutes to get to Ebbsfleet. If you leave in 20 minutes, you should be on platform $platform at $arrivalTime and in time for $trainDeparture train. $directions This places you at work at around $atWork";
        }
    }

    public function kossEveningCommuteAdvanceNotice()
    {

        $drivingTimes = $this->googleDrivingTime( "ebbsfleet+international", "home");

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];

        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 17;
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
            $this->lineOne = "Good evening creator.";
            $this->lineTwo = "You should make the $trainDeparture train (platform $platform). Roads are $drivingCondition, will take $drivingTime min to drive home, getting you there at $atHome";
            $this->sendToIffft("K");
        }
    }

    private function nationalRailNextFromPlatform($darwinResponse, $platforms) {
        foreach ($darwinResponse->departures->all as $item) {
            if (in_array($item->platform, $platforms)) return $item->service_timetable->id;
        }
    }

    public function processHeathRoadTurn($turn, $alternative) {

        if ($turn == "turn-right") {
            return "Dean Street is faster. (Loose Rd - $alternative min).";
        }
        else return "Loose Road is faster. (Dean St - $alternative min).";

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

    private function process_response($data)
    {


        /*switch ($this->hook) {
            case "waterloo_east_trigger":
                $departingStn = "LBG";
                $arrivalStn = "MRN";
                $drivingTimes = $this->get_google_driving_time("marden+station", "51.231953,0.504038");
                $drivingTime = $drivingTimes[1];
                $trafficRatio = $drivingTimes[2];
                $drivingCondition = assign_traffic_condition($trafficRatio);
                $result[4] = $drivingCondition . " - $drivingTime minutes";
                $walkingTime = 5;
                break;
            case "p_wood_trigger":
                $departingStn = "PDW";
                $arrivalStn = "MRN";
                $drivingTimes = $this->get_google_driving_time("marden+station", "51.231953,0.504038");
                $drivingTime = $drivingTimes[1];
                $trafficRatio = $drivingTimes[2];
                $drivingCondition = $this->assign_traffic_condition($trafficRatio);
                $result[4] = $drivingCondition . " - $drivingTime minutes";
                $walkingTime = 5;
                break;
            case "wake_up_trigger":
                $departingStn = "MRN";
                $arrivalStn = "CHX";
                break;
            case "leave_work_trigger":
                $departingStn = "CHX";
                $arrivalStn = "MRN";
                break;
        }*/


        //[0] is train's arrival to destination
        //[1] is person's arrival to destination
        //[2] is status of train
        //[3] estimated departure time of train
        //[4] traffic conditions
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
            return 0;
        }

        try {

            $expiresAt = now()->addMinutes(5);
            Cache::put($this->action.$api , true, $expiresAt);

            $hook = $this->hookUp($api, [
                'API_VAR1' => $this->lineOne,
                'API_VAR2' => $this->lineTwo,
                'API_ACTION' => $this->action
            ], $this->debug
            );

            echo $hook->fullResponse;
        } catch (\Exception $e) {

            Cache::forget($api);
            if ($this->debug) echo "500, Error passing information to IFTTT $e";
            abort('500', "Error passing information to IFTTT $e");

        }
    }


//////
///
/// LOGIC BEGINS
///
/////

    /*
    if ($hook == "waterloo_east_trigger")
    {

    die_if_outside_hours([14, 22], ["Wed", "Sat", "Sun"]);

    $mainJsonResponse = national_rail_station_live("LBG", "MRN");
    $times = get_nth_train($mainJsonResponse, 0);
    $timeMarden = $times[0];
    $timeHome = $times[1];
    $statusTrain = $times[2];
    $statusRoad = $times[4];

    $value1 = "Dear Mrs Pikisso. ETA $timeHome";
    $value2 = "Koss is en route home and is now around Waterloo East. Train is $statusTrain due to arrive to Marden at $timeMarden. Traffic home is $statusRoad, ETA $timeHome. Have a wonderful evening.";


    }

    if ($hook == "leave_work_trigger") {

        die_if_outside_hours([0, 24], ["Sat", "Sun"]);

        $mainJsonResponse = national_rail_station_live("CHX", "MRN");
        $drivingTimes = get_google_driving_time("marden+station", "51.231953,0.504038");
        $drivingTime = $drivingTimes[0];
        $usualDrivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];
        $drivingCondition = assign_traffic_condition($trafficRatio);
        $timeToStation = $drivingTime + 5;

        for ($x = 0; $x < 2; $x++) {
            $processed = get_nth_train($mainJsonResponse, $x);
            $leaveTime = date('H:i', strtotime("$processed[3] - 28 minutes"));
            $homeTime = date('H:i', strtotime("$processed[0] + $timeToStation minutes"));
            $value2 .= "$leaveTime/$processed[3]-$processed[0]/$homeTime($processed[2]) ";
        }

        $value2 .= "$drivingCondition traffic, $drivingTime";

        $value1 = "Good Evening!";

    }

    if ($hook == "p_wood_trigger") {

        die_if_outside_hours([15, 23], ["Wed", "Sat", "Sun"]);

        $mainJsonResponse = national_rail_station_live("PDW", "MRN");
        $times = get_nth_train($mainJsonResponse, 0);
        $timeMarden = $times[0];
        $timeHome = $times[1];
        $statusTrain = $times[2];
        $statusRoad = $times[4];

        $value1 = "Train update";
        $value2 = "ETA $timeHome. Train $statusTrain will soon approach Paddock Wood.";


    }

    if ($hook == "wake_up_trigger") {

        die_if_outside_hours([06, 10], ["Wed", "Sat", "Sun"]);

        $mainJsonResponse = national_rail_station_live("MRN", "CHX");
        $drivingTimes = get_google_driving_time("51.231953,0.504038", "marden+station");
        $drivingTime = $drivingTimes[0];
        $usualDrivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];
        $timeToStation = $drivingTime + 5;

        for ($x = 0; $x < 3; $x++) {
            $processed = get_nth_train($mainJsonResponse, $x);
            $leaveTime = date('H:i', strtotime("$processed[3] - $timeToStation minutes"));
            $workTime = date('H:i', strtotime("$processed[0] + 25 minutes"));
            $value2 .= "$leaveTime/$processed[3]-$processed[0]/$workTime($processed[2]) ";
        }

        $drivingCondition = assign_traffic_condition($trafficRatio);
        $value2 .= $drivingCondition . " - $drivingTime minutes";

        $value1 = "Good morning!";

    }

    if ($hook == "lizzie_commute_home") {

        die_if_outside_hours([0, 24], ["Sat", "Sun"]);

        //$walkingTime = 10;
        $drivingTimes = get_google_driving_time("fremlin+walk", "51.231953,0.504038");
        $drivingTime = $drivingTimes[0];
        $trafficRatio = $drivingTimes[2];
        $timeHome = $drivingTime;//+$walkingTime;
        $drivingCondition = assign_traffic_condition($trafficRatio);

        $value2 = "Traffic update, at the moment the roads are ";
        $value2 .= $drivingCondition . " - it will take you $timeHome minutes to drive home. ";// to get home. ";

        $weather = get_weather_in_maidstone();
        $value2 .= $weather;

        $value1 = "Good evening Mrs Pikisso!";

    }

    // FINAL ASSIGN

    $signature = " Your Smart Home";
    $value .= $signature;
    $trigger_url = $baseUrl . "value1=$value1";
    if ($value2) $trigger_url .= "&value2=$value2";
    if ($value3) $trigger_url .= "&value3=$value3";


    //CHECK FOR DEBUG

    if ($_GET["code"] != 7501) debug($trigger_url, $value1, $value2);

    //EXECUTE IFTTT response

    $data = trigger($trigger_url);

    */
}
