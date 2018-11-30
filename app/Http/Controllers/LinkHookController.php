<?php

namespace App\Http\Controllers;

use App\LinkHook;

class LinkHookController extends Controller
{

    public $action;
    public $hook;
    public $code;
    public $debug;
    public $curfew;
    public $client;

    /**
     * LinkHookController constructor.
     * @param $ifttt
     */
    public function __construct($action = null, $hook = null)
    {
        $this->debug = false;

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
                case "WE":
                    if (!$this->debug) $this->dieOfCurfew(['15', '21'], ['Wed', 'Sat', 'Sun'], []);
                    $this->waterlooEast();
                    break;
                case "PW":
                    if (!$this->debug) $this->dieOfCurfew(['16', '22'], ['Wed', 'Sat', 'Sun'], []);
                    $this->paddockWood();
                    break;
                case "WU":
                    if (!$this->debug) $this->dieOfCurfew(['6', '12'], ['Wed', 'Sat', 'Sun'], ['text']);
                    $this->wakeUp();
                    break;
                case "LC":
                    if (!$this->debug) $this->dieOfCurfew(['15', '21'], ['Sat', 'Sun'], []);
                    $this->lizzieCommute();
                    break;
            }

        }

    }

    public function dieOfCurfew($time, $days, $actions)
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
            $departingStn = "LBG";
            $arrivalStn = "MRN";
            $drivingTimes = $this->googleDrivingTime("marden+station", "51.231953,0.504038");
        } catch (\Exception $e) {
            abort('500', "Error getting information from Google");
        }

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];
        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 5;

        try {
            $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn);
            $times = $this->nationalRailSpecificTrain($mainResponse->departures->all[0]->service_timetable->id, $departingStn, $arrivalStn);
        } catch (\Exception $e) {
            abort('500', "Error getting information from Darwin");
        }

        $timeMarden = $times[0];
        $timeAfterMardenInMinutes = $drivingTime + $walkingTime;
        $timeHome = date('H:i', strtotime("$timeMarden + $timeAfterMardenInMinutes minutes"));
        $statusTrain = $times[2];

        $values[1] = "Dear Mrs Pikisso. ETA $timeHome";
        $values[2] = "Koss is en route home and is now around Waterloo East. Train is $statusTrain due to arrive to Marden at $timeMarden. Traffic home is $drivingCondition, ETA $timeHome. Have a wonderful evening.";

        try {
            $hook = new LinkHook('I', ['values' => $values, 'action' => $this->action]);
            return $hook->fullResponse;
        } catch (\Exception $e) {
            abort('500', "Error passing information to IFTTT");
        }

    }

    private function googleDrivingTime($from, $to)
    {
        $hook = new LinkHook('G', ['from' => $from, 'to' => $to]);
        $response = $hook->objectResponse;

        $duration = $response->routes[0]->legs[0]->duration->value;
        $duration_in_traffic = $response->routes[0]->legs[0]->duration_in_traffic->value;
        $ratio = (($duration_in_traffic - $duration) / $duration) * 100;
        $in_minutes = $duration / 60;
        $in_minutes_in_traffic = $duration / 60;
        $round_mins = round($in_minutes, 0);
        $round_mins_in_traffic = round($in_minutes_in_traffic, 0);
        $round_ratio = round($ratio);

        return [$round_mins, $round_mins_in_traffic, $round_ratio];
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

    private function nationalRailStationLive($departingStn, $arrivalStn)
    {
        $hook = new LinkHook('R', ['from' => $departingStn, 'to' => $arrivalStn]);

        return $hook->objectResponse;
    }

    private function nationalRailSpecificTrain($detailedUrl, $departingStn, $arrivalStn)
    {

        $hook = new LinkHook("Z", ['url' => $detailedUrl]);
        $data = $hook->objectResponse;

        $result = [];
        foreach ($data->stops as $stop) {

            if ($stop->station_code == $departingStn) {

                $result[2] = $this->trainStatus($stop->status);

                if (!empty($stop->expected_departure_time)) $result[3] = $stop->expected_departure_time;
                else if (!empty($stop->aimed_departure_time)) $result[3] = $stop->aimed_departure_time;
                else $result[3] = "UNKNOWN";
            }

            if ($stop->station_code == $arrivalStn) {
                if (!empty($stop->expected_arrival_time)) $result[0] = $stop->expected_arrival_time;
                else if (!empty($stop->aimed_arrival_time)) $result[0] = $stop->aimed_arrival_time;
                else $result[0] = "UNKNOWN";

                $result[1] = date('H:i', strtotime("$result[0]"));
            }
        }

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
    public function index()
    {
        echo "Link system active";
    }

    public function paddockWood()
    {

    }

    public function wakeUp()
    {
        try {
            $departingStn = "MRN";
            $arrivalStn = "LBG";
            $drivingTimes = $this->googleDrivingTime("marden+station", "51.231953,0.504038");
        } catch (\Exception $e) {
            abort('500', "Error getting information from Google");
        }

        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];
        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 5;

        try {
            $mainResponse = $this->nationalRailStationLive($departingStn, $arrivalStn);
            $times = $this->nationalRailSpecificTrain($mainResponse->departures->all[0]->service_timetable->id, $departingStn, $arrivalStn);
        } catch (\Exception $e) {
            abort('500', "Error getting information from Darwin");
        }

        $timeMarden = $times[0];
        $timeAfterMardenInMinutes = $drivingTime + $walkingTime;
        $timeHome = date('H:i', strtotime("$timeMarden + $timeAfterMardenInMinutes minutes"));
        $statusTrain = $times[2];

        $values[1] = "Dear Mrs Pikisso. ETA $timeHome";
        $values[2] = "Koss is en route home and is now around Waterloo East. Train is $statusTrain due to arrive to Marden at $timeMarden. Traffic home is $drivingCondition, ETA $timeHome. Have a wonderful evening.";

        try {
            $hook = new LinkHook('I', ['values' => $values, 'action' => $this->action]);
            return $hook->fullResponse;
        } catch (\Exception $e) {
            abort('500', "Error passing information to IFTTT");
        }

    }

    public function lizzieCommute()
    {

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
        $hook = new LinkHook ("W", ['city' => 'maidstone, uk']);
        $weather = $hook->objectResponse;

        if ($weather->success == true) {

            $degrees = $weather->response->ob->tempC;
            $feels = $weather->response->ob->feelslikeC;
            $description = $weather->response->ob->weather;

            return "The weather in Maidstone is $description. $degrees*C (feels like $feels*C)";

        } else return "There was an error getting the weather";

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
