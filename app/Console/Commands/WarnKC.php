<?php

namespace App\Console\Commands;

use App\Http\Controllers\LinkHookController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WarnKC extends Command
{

    public $controller;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:check:lww {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for late to work warnings';

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
        $this->controller->kossMorningCommute(22);
        $workBegins = Carbon::createFromTimeString("9:30");
        $estimatedArrival = Carbon::createFromTimeString($this->controller->spareVariable);

        if ($estimatedArrival->gt($workBegins) && !$debug) {
            $this->controller->kossAlternativeCommute(22);
            $this->controller->lineOne = "LATE TO WORK WARNING";
            $this->controller->lineTwo .= ". " . $this->controller->lineSpare;
            $this->controller->action = "notification";

            $this->controller->sendToIffft("K");

            $logValue = [
                "date" => Carbon::today()->toDateString(),
                "timeWarned" => Carbon::today()->toTimeString(),
                "estimatedArrival" => $estimatedArrival
            ];
            $this->controller->log("historic-information", "late-to-work-prediction", json_encode($logValue));
        }

        else if ($debug) {
            $this->controller->kossAlternativeCommute(22);
            $this->controller->lineOne = "LATE TO WORK WARNING";
            $this->controller->lineTwo .= ". " . $this->controller->lineSpare;

            $this->info($this->controller->lineOne);
            $this->info($this->controller->lineTwo);
        }
    }
}
