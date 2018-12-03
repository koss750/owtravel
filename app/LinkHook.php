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
    public $type;
    public $objectResponse;
    public $fullResponse;
    public $client;
    public $params;
    public $config;

    public function __construct($type, $params)
    {
        $this->client = new GuzzleHttp\Client();
        $this->config = config('app.link_system');
        $this->type = $type;
        $this->params = $params;

        $this->url = $this->config[$type]["url"] ?? $this->params["url"] ?? abort(500, "URL cannot be established for the LinkHook of type $type");

        $this->processMe();
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
            $this->insertParam();
        } catch (\Exception $e) {}

        try {
            $this->fullResponse = $response->getBody()->getContents();
            $this->objectResponse = json_decode($this->fullResponse);
        } catch (\Exception $e) {
            abort (500, "Could not process response from hook");
        }

    }

    public function insertParams() {

        $typeSpecificParams = $this->config[$this->type]["params"];
        foreach ($typeSpecificParams as $specificParam) {
            $this->url = str_replace($specificParam, $this->params[$specificParam], $this->url);
        }

    }

}