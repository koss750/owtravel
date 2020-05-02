<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckHW extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:hw {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run HW comparison between now and 1 hour later';

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

        $this->controller->oneHourWeather($starting->subHour(1));
        $chanceLater[0] = $this->controller->lineOne["chanceLater"];
        $intLater[0] = $this->controller->lineOne["intensityLater"];

        $this->controller->oneHourWeather($starting);
        $chanceLater[1] = $this->controller->lineOne["chanceLater"];
        $intLater[1] = $this->controller->lineOne["intensityLater"];

        $rainIndex0h = $chanceLater[0] * $intLater[0];
        $rainIndex1h = $chanceLater[1] * $intLater[1];
        $rainIndexIncreasing = ($rainIndex0h<$rainIndex1h);

        if ($rainIndex0h == $rainIndex1h && $rainIndex1h == 0) {
            $this->controller->lineOne = "Clear";
            $this->controller->lineTwo = "No Rain to develop within the hour";
        } else if ($rainIndexIncreasing && $rainIndex1h > 100 && $rainIndex0h < 100) {
            $this->controller->lineOne = "Rain Warning!";
            $this->controller->lineTwo = "Rain to start within the hour";
        }

        $this->info($this->controller->lineOne);
        $this->info($this->controller->lineTwo);
        $this->controller->action = "notification";
        if (!$debug) {
            $this->controller->sendToIffft("K");
            //$this->controller->sendToIffft("L");
        }

    }
}
