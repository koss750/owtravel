<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Illuminate\Console\Command;

class CompareEWin1Hour extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:ew:compare';

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
        $this->controller->oneHourWeather();

        if (($this->controller->lineOne["chanceNow"] < $this->controller->lineOne["chanceLater"]) || ($this->controller->lineOne["intensityNow"] < $this->controller->lineOne["intensityLater"])) {
            $chanceLater = $this->controller->lineOne["chanceLater"];
            $intNow = $this->controller->lineOne["intensityNow"];
            $intLater = $this->controller->lineOne["intensityLater"];
            $this->controller->lineOne = "Good evening! Weather warning!";
            $this->controller->lineTwo = "Chance of rain in 1 hour is getting higher at $chanceLater%. Intensity now is $intNow and will become $intLater";
            $this->info($this->controller->lineOne);
            $this->info($this->controller->lineTwo);
            $this->controller->action = "notification";
            $this->controller->sendToIffft("K");
            $this->controller->sendToIffft("L");
        }

    }
}
