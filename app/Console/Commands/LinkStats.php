<?php

namespace App\Console\Commands;

use App\Document;
use App\Link;
use App\User;
use Illuminate\Console\Command;

class LinkStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:link:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the number of LinkHooks called';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $googleMaps = Link::where('type', 'GOOGLE_MAPS')->get()->count();
        $ifttt = Link::where('type', 'LIKE', 'IFT%')->get()->count();
        $rail = Link::where('type', 'NATIONAL_RAIL')->get()->count();
        $rail2 = Link::where('type', 'BASIC')->get()->count();
        $this->info($googleMaps);
        $this->info($ifttt);
        $this->info($rail+$rail2);
        die();
        $headers = ['Document', 'Number'];

        foreach ($docs as $doc) {

            $data[] =
                [
                    'Document' => $doc->description,
                    'Number' => $doc->number,
                ];

        }

        $this->table($headers, $data);
    }
}
