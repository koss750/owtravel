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
        $times = $this->controller->spareVariable;
        $difference = ($this->controller->processKossCommuteTimeDifference());
        $this->controller->kossMorningCommute();

        $first = Carbon::parse($times[0]);
        $second = Carbon::parse($times[1]);
        if (!$first->gt($second)) $this->controller->lineOne = "Long drive recommended. $times[0] $times[1] (-$difference min)";
        else $this->controller->lineOne = "Short drive recommended. $times[0] $times[1] (+$difference min)";

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

        $workBegins = Carbon::createFromTimeString("9:24");
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
