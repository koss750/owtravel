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
        $this->type = $type;
        $this->config = config('app.link_system');
        $this->config = $this->config[$this->type];
        $this->params = $params;

        $this->url = $this->config["url"] ?? $this->params["url"] ?? abort(500, "URL cannot be established for the LinkHook of type $type");
        $this->processMe();
    }

    public function processMe() {

        if (!isset($this->url)) {
            abort (500, "Hook Url Not Set");
        }

        try {
            $this->insertParams();
        } catch (\Exception $e) {
            abort (500, "Could not process hook");
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

    public function insertParams() {

        $typeSpecificParams = $this->config["params"];
        foreach ($typeSpecificParams as $item) {
            $this->url = str_replace($item, $this->params[$item], $this->url);
        }

    }

}
