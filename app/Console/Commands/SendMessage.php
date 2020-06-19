<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Illuminate\Console\Command;

class SendMessage extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:sm {--sms}{--debug}{--K}{--L}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a message';

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
        $sms = $this->option('sms') ?? 0;
        $action = $sms ? "sms" : "notification";

        $this->controller->action = $action;

        $this->controller->lineOne = $this->ask("First Line: ");
        $this->controller->lineTwo = $this->ask("Second Line: ");

        $this->line("Sending $action to K and L");
        $this->info($this->controller->lineOne);
        $this->info($this->controller->lineTwo);

        if (!$debug) {
            if (!($this->option('K'))&&!($this->option('L'))) {
                $this->controller->sendToIffft("K");
                $this->controller->sendToIffft("L");
            }
            if ($this->option('K')) $this->controller->sendToIffft("K");
            if ($this->option('L')) $this->controller->sendToIffft("L");
        }
    }
}
