<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompareEWin1Hour extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:ew:compare {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run EW comparison between now and 1 hour later';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->controller = new LinkHookController();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $debug = $this->option('debug');
        $starting = Carbon::now();

        $this->controller->oneHourWeather($starting);
        $chanceLater[0] = $this->controller->lineOne["chanceLater"];
        $intLater[0] = $this->controller->lineOne["intensityLater"];

        $this->controller->oneHourWeather($starting->addHour(1));
        $chanceLater[1] = $this->controller->lineOne["chanceLater"];
        $intLater[1] = $this->controller->lineOne["intensityLater"];

        $rainIndex1h = $chanceLater[0] * $intLater[0];
        $rainIndex2h = $chanceLater[1] * $intLater[1];

        if ($rainIndex2h == $rainIndex1h && $rainIndex1h == 0) {
            $this->controller->lineOne = "Good evening! Weather update";
            $this->controller->lineTwo = "No rain at 9pm and 10pm";
        } else if ($rainIndex2h == 0) {
            $this->controller->lineOne = "Good evening! Weather update";
            $this->controller->lineTwo = "9pm - $chanceLater[0]%, int.$intLater[0]. No rain at 10pm";
        } else if ($rainIndex1h == 0) {
            $this->controller->lineOne = "Good evening! Weather update";
            $this->controller->lineTwo = "No rain at 9pm. 10pm - $chanceLater[1]%, int. $intLater[1]";
        } else {
            $this->controller->lineOne = "Good evening! Weather warning";
            $this->controller->lineTwo = "9pm - $chanceLater[0]%, int.$intLater[0]. 10pm - $chanceLater[1]%, int. $intLater[1]. ";
        }

        if (($chanceLater[1] * $intLater[1]) > 1000) {
            $this->controller->oneHourWeather($starting->addHour(2));
            $chanceLater[2] = $this->controller->lineOne["chanceLater"];
            $intLater[2] = $this->controller->lineOne["intensityLater"];
            $rainIndex3h = $chanceLater[2] * $intLater[2];
            $this->controller->lineOne = "Good evening! Weather warning";
            if ($rainIndex2h > $rainIndex3h) {
                $this->controller->lineTwo .= "11pm - $chanceLater[2]%, int. $intLater[2]";
            }
        }
        $this->info($this->controller->lineOne);
        $this->info($this->controller->lineTwo);
        $this->controller->action = "notification";
        if (!$debug) {
            $this->controller->sendToIffft("K");
            $this->controller->sendToIffft("L");
        }

    }
}
