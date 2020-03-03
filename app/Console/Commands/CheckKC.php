<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckKC extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:kc {--debug}';

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
        $debug = $this->option('debug');
        if ($this->controller->checkWeekendRegimeToday()) {
            $this->info("Today is instructed to be a weekend regime day. Skipping operation");
            return;
        }
        $this->controller->kossCompareCommutes();
        $difference = ($this->controller->processKossCommuteTimeDifference());
        $this->controller->kossMorningCommute();

        $this->controller->lineOne = "Good morning creator. Don't be late!";
        $this->controller->lineTwo .= " Alternative route ";
        if ($difference<0) {
            $this->controller->kossAlternativeCommute();
            $this->controller->lineTwo .= "$difference minutes faster. " . $this->controller->lineSpare;
        }
        else if ($difference>0) {
            $this->controller->kossAlternativeCommute();
            $this->controller->lineTwo .= "$difference minutes slower. " . $this->controller->lineSpare;
        }
        else $this->controller->lineTwo .= "has the same ETA";

        $workBegins = Carbon::createFromTimeString("9:44");
        $estimatedArrival = Carbon::createFromTimeString($this->controller->spareVariable);
        $runningLate = $estimatedArrival->gt($workBegins);

        $this->info($this->controller->lineOne);
        $this->info($this->controller->lineTwo);
        $this->controller->action = "notification";
        if (!$debug && $runningLate) {
            $this->controller->sendToIffft("K");
        }
    }
}
