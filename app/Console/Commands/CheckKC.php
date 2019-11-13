<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Illuminate\Console\Command;

class CheckKC extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:kc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check kc time proportion';

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
        $times = $this->controller->compareDrivingTimes("home", "ebbsfleet+international", "&waypoints=via:Dean+street+maidstone", "&waypoints=via:loose+road+maidstone");
        $this->info("Drive: " . $times[1]);
        if($times[1]>49) {
            $this->controller->kossMorningCommute();
            $this->controller->lineOne = "Good morning creator. Commute Issue. ";
            $this->controller->action = "notification";
            $this->controller->sendToIffft("K");
        }
    }
}
