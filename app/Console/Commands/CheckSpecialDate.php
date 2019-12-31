<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSpecialDate extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:special {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for special dates';

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
        $specialOccasion = false;

        $debug = $this->option('debug');
        $date = Carbon::now();
        $timeString = $date->format('d m y');

        $this->controller->lineOne = "A special message from your smart home";
        $this->controller->lineTwo = "[no message]";
        $this->controller->action = "[no action]";

        if ($timeString == "01 01 20") {
            $specialOccasion = true;
            $this->controller->lineOne = "A special message from your smart home";
            $this->controller->lineTwo = "Happy New Year!!! Let 2020 be great";
            $this->controller->action = "notification";
        }
        if ($specialOccasion && !$debug) {
            $this->controller->sendToIffft("K");
            $this->controller->sendToIffft("L");
        } else {
            $this->info($this->controller->lineOne);
            $this->info($this->controller->lineTwo);
            $this->info($this->controller->action);
        }
    }
}
