<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Illuminate\Console\Command;

class CheckLondonViaEbbs extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:ebbs {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check what time will one get to London if left home in 5 mins';

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
        $this->controller->goingToLondonViaEbbsfleet();
        $this->info($this->controller->lineOne);
        $this->info($this->controller->lineTwo);
        if (!$debug) {
            $this->controller->action = "notification";
            $this->controller->sendToIffft("K");
        }
    }
}
