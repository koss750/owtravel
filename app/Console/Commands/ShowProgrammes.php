<?php

namespace App\Console\Commands;

use App\TravelProgramme;
use Illuminate\Console\Command;

class ShowProgrammes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:programmes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows travel programmmes recorded';

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
        $programmes = TravelProgramme::all();
        $headers = ['Title', 'Destination'];



        /* Note: the following would work as well:
        $data = [
            ['Jim', 'Meh'],
            ['Conchita', 'Fabulous']
        ];
        */

        $data = array();

        foreach ($programmes as $programme) {

            $data[] =
                [
                    'Title' => $programme->name,
                    'Destination' => $programme->main_destination,
                ];

        }

        $this->table($headers, $data);
    }
}
