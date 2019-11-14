<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Illuminate\Console\Command;

class CheckLc extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:lc {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run LC notification';

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
        $this->controller->lizzieMorningCommute();
        $this->info($this->controller->lineOne);
        $this->info($this->controller->lineTwo);
        if (!$debug) {
            $this->controller->action = "notification";
            $this->controller->sendToIffft("L");
        }

    }
}
