<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as Guzl;

class LinkHookController extends Controller
{

    public $action;
    public $hook;
    public $code;
    private $base_url;

    /**
     * LinkHookController constructor.
     * @param $ifttt
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function index()
    {
        echo "Link system active";
    }

    public function waterlooEast($ifttt)
    {
        $this->action = $ifttt;
        //$this->dieIfOutsideHours([14, 22], ["Wed", "Sat", "Sun"]);

        $departingStn = "LBG";
        $arrivalStn = "MRN";
        $drivingTimes = $this->googleDrivingTime("marden+station", "51.231953,0.504038");
        $drivingTime = $drivingTimes[1];
        $trafficRatio = $drivingTimes[2];
        $drivingCondition = $this->trafficCondition($trafficRatio);
        $walkingTime = 5;

        $mainJsonResponse = $this->nationalRailStationLive($departingStn, $arrivalStn);
        $times = $this->nationalRailSpecificTrain($mainJsonResponse, 0, $departingStn, $arrivalStn);
        $timeMarden = $times[0];
        $timeHome = date('H:i', strtotime("$timeMarden + $drivingTime + $walkingTime"));
        $statusTrain = $times[2];
        $statusRoad = $drivingCondition . " - $drivingTime minutes";

        $value1 = "Dear Mrs Pikisso. ETA $timeHome";
        $value2 = "Koss is en route home and is now around Waterloo East. Train is $statusTrain due to arrive to Marden at $timeMarden. Traffic home is $statusRoad, ETA $timeHome. Have a wonderful evening.";

        $url = $this->constructUrl([$value1, $value2]);
        dd($url);

    }

    private function constructUrl($values)
    {

        // FINAL ASSIGN

        $signature = " Your Smart Home";
        $value1 = $values[0];
        $value2 = $values[1];
        $value2 .= $signature;
        $trigger_url = $this->base_url . "value1=$value1";
        if ($value2) $trigger_url .= "&value2=$value2";

        return $trigger_url;

    }

    private function trigger($url)
    {

        $client = new Guzl;
        $client->get($url); // 200

    }

    private function debug($url, $line_one, $line_two)
    {

        if ($_GET["action"] == "sms") {
            $prefix = "<strong>SMS to 07482428982</strong>:<br>";
            $line_one .= "<br>";
        } else if ($_GET["action"] == "notification") {
            $prefix = "IFTTT Notification:<br>";
            $line_one = "<br><strong>" . $line_one . "</strong><br>";
        }

        $url = "URL : $url <br><br>";

        echo $url . $prefix . $line_one . $line_two;
        die();

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

    private function nationalRailSpecificTrain($json, $n, $departingStn, $arrivalStn)
    {

        $detailedUrl = $json->departures->all[$n]->service_timetable->id;
        $deep_response = file_get_contents($detailedUrl);
        $data = json_decode($deep_response);
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

    private function nationalRailStationLive($departingStn, $arrivalStn)
    {
        $response = file_get_contents("https://transportapi.com/v3/uk/train/station/$departingStn/live.json?app_id=429b0914&app_key=11628415a6ae399d6b46ea2c4511f074&calling_at=$arrivalStn&darwin=true&train_status=passenger");
        return json_decode($response);
    }

    private function googleDrivingTime($from, $to)
    {
        $api_url = "https://maps.googleapis.com/maps/api/directions/json?origin=$from&destination=$to&departure_time=now&language=en&key=AIzaSyCT2G-01NRshhoFDT4GLInBTiIlvra5fIk";
        $json = json_decode(file_get_contents($api_url));
        $duration = $json->routes[0]->legs[0]->duration->value;
        $duration_in_traffic = $json->routes[0]->legs[0]->duration_in_traffic->value;
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

    private function get_weather_in_maidstone()
    {


        // fetch Aeris API output as a string and decode into an object
        $response = file_get_contents("https://api.aerisapi.com/observations/maidstone, uk?&format=json&filter=allstations&limit=1&fields=ob.tempC,ob.weather,ob.feelslikeC&client_id=x2iSePGhU7SK1jkEMCxbK&client_secret=nyEnPsCchakT5uCC7LkhwvO2YyWuU9kCcZkZdXs6");
        $json = json_decode($response);
        if ($json->success == true) {

            $degrees = $json->response->ob->tempC;
            $feels = $json->response->ob->feelslikeC;
            $description = $json->response->ob->weather;

            return "The weather in Maidstone is $description. $degrees*C (feels like $feels*C)";
        } else {
            return "There was an error getting the weather";
        }

    }

    private function dieIfOutsideHours($time, $days)
    {

        if ($this->code !=7501) {
            //echo "test run, ignoring outside hours<br>";
        } else {
            $timeNow = date("H");
            $dayNow = date("D");

            if ($timeNow < $time[0] || $timeNow > $time[1]) {
                abort(403, "Outside working hours");
            }

            foreach ($days as $day) {
                if ($day == $dayNow) {
                    abort(403, "Outside working days");
                }
            }
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
    $trigger_url = $base_url . "value1=$value1";
    if ($value2) $trigger_url .= "&value2=$value2";
    if ($value3) $trigger_url .= "&value3=$value3";


    //CHECK FOR DEBUG

    if ($_GET["code"] != 7501) debug($trigger_url, $value1, $value2);

    //EXECUTE IFTTT response

    $data = trigger($trigger_url);

    */
}
