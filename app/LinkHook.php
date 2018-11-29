<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 28/11/2018
 * Time: 22:47
 */

namespace App;

use GuzzleHttp;

class LinkHook extends BaseModel
{
    public $url;
    public $objectResponse;
    public $fullResponse;
    public $client;

    public function __construct($type, $params)
    {
        $this->client = new GuzzleHttp\Client();

        switch ($type) {
            case "G":
                $from = $params['from'];
                $to = $params['to'];
                $this->constructGoogleUrl($from, $to);
                $this->processMe();
                break;
            case "I":
                $values = $params['values'];
                $action = $params['action'];
                $this->constructIftttUrl($values, $action);
                $this->processMe();
                break;
            case "W":
                $city = $params['city'];
                $this->constructWeatherUrl($city);
                $this->processMe();
                break;
            case "R":
                $from = $params['from'];
                $to = $params['to'];
                $this->constructRailUrl($from, $to);
                $this->processMe();
                break;
            case "Z":
                $this->url = $params['url'];
                $this->processMe();
                break;
        }
    }

    public function processMe() {

        if (!isset($this->url)) {
            abort (500, "Hook Url Not Set");
        }

        try {
            $response = $this->client->get($this->url);
        } catch (\Exception $e) {
            abort (500, "Could not process hook");
        }

        try {
            $this->fullResponse = $response->getBody()->getContents();
            $this->objectResponse = json_decode($this->fullResponse);
        } catch (\Exception $e) {
            abort (500, "Could not process response from hook");
        }

    }

    public function constructIftttUrl($values, $action)
    {
        $baseUrl = env('LINK_SYSTEM_IFTTT_URL', '');
        $key = env('LINK_SYSTEM_IFTTT_KEY', '');
        $url = str_replace('API_KEY', $key, $baseUrl);
        $url = str_replace('API_ACTION', $action, $url);

        $signature = " Your Smart Home";
        $value1 = $values[1];
        $value2 = $values[2];
        $value2 .= $signature;

        $url = str_replace('API_VALUE_1', $value1, $url);
        $url = str_replace('API_VALUE_2', $value2, $url);

        $this->url = $url;
    }

    public function constructGoogleUrl($from, $to)
    {
        $baseUrl = env('LINK_SYSTEM_GOOGLE_MAPS_URL', '');
        $key = env('LINK_SYSTEM_GOOGLE_MAPS_KEY', '');
        $url = str_replace('API_KEY', $key, $baseUrl);

        $url = str_replace('API_TO', $to, $url);
        $url = str_replace('API_FROM', $from, $url);

        $this->url = $url;
    }

    public function constructWeatherUrl($city)
    {
        $baseUrl = env('LINK_SYSTEM_WEATHER_URL', '');
        $key = env('LINK_SYSTEM_WEATHER_KEY', '');
        $url = str_replace('API_KEY', $key, $baseUrl);

        $url = str_replace('API_CITY', $city, $url);

        $this->url = $url;
    }

    public function constructRailUrl($from, $to)
    {
        $baseUrl = env('LINK_SYSTEM_NATIONAL_RAIL_URL', '');
        $key = env('LINK_SYSTEM_NATIONAL_RAIL_KEY', '');
        $id = env('LINK_SYSTEM_NATIONAL_RAIL_ID', '');
        $url = str_replace('API_KEY', $key, $baseUrl);
        $url = str_replace('API_ID', $id, $url);

        $url = str_replace('API_TO', $to, $url);
        $url = str_replace('API_FROM', $from, $url);

        $this->url = $url;
    }

}