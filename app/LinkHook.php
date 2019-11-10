<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 28/11/2018
 * Time: 22:47
 */

namespace App;

use GuzzleHttp;
use Illuminate\Support\Facades\Cache;

class LinkHook extends BaseModel
{

    public $url;
    public $type;
    public $objectResponse;
    public $fullResponse;
    public $client;
    public $params;
    public $debug;
    public $config;

    public function __construct($type, $params, $debug = false)
    {
        $this->client = new GuzzleHttp\Client();
        $this->type = $type;
        $this->config = config('app.link_system');
        $this->config = $this->config[$this->type];
        $this->params = $params;
        $this->debug = $debug;
        $this->url = $this->config["url"] ?? $this->params["url"] ?? abort(500, "URL cannot be established for the LinkHook of type $type");
        $this->processMe();
    }

    public function processMe() {

        if (!isset($this->url)) {
            if ($this->debug) echo "500, Hook Url Not Set";
            else abort (500, "Hook Url Not Set");
        }

        try {
            if ($this->debug) echo "inserting params for $this->type <br>";
            $this->insertParams();
            if ($this->debug) echo ($this->url . "<br>");
        } catch (\Exception $e) {
            if ($this->debug) report($e);
            else abort (500, "Failed inserting params into hook url");
	    }

	    $hashKey = substr(md5($this->url), 0, 8);

        $this->fullResponse = Cache::remember($hashKey, 5, function () {
            try {
                $response = $this->client->get($this->url);
                return $response->getBody()->getContents();
            } catch (\Exception $e) {
                if ($this->debug) report($e);
                else abort (500, "Could not get response from hook");
                return 0;
            }
        });

        try {
            $this->objectResponse = json_decode($this->fullResponse);
            $this->generateAuditEntry();
        } catch (\Exception $e) {
            if ($this->debug) report($e);
            else abort (500, "Failed while processing response from hook $this->type $e");
        }

    }

    public function insertParams() {

        $typeSpecificParams = $this->config["params"];
        foreach ($typeSpecificParams as $item) {
            $this->url = str_replace($item, $this->params[$item], $this->url);
        }

    }

    private function generateAuditEntry() {

        try {
            $audit = new Link();
            $audit->url=$this->url;
            $audit->type=$this->type;
            $audit->object_response=json_encode($this->objectResponse);
            $audit->full_response=$this->fullResponse;
            $audit->params=json_encode($this->params);
            $audit->debug=$this->debug ?? false;
            $audit->save();
        } catch (\Exception $e) {
            if ($this->debug) report($e);
            else abort (500, "Failed while generating audit entry. $e");
        }

    }

}
