<?php

namespace App\Console\Commands;

use App\Country;
use App\Document;
use App\DocumentTypes;
use App\Kospital;
use App\Suitcase;
use App\User;
use Illuminate\Console\Command;

class ShowMedicine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:suitcase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a Suitcase';

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
        $suitcase = new Suitcase();

        $suitcase->destination = strtoupper($this->ask('Enter the code of the destination country'));
        $suitcase->origin = strtoupper($this->ask('Enter the code of the origin country'));
        $suitcase->duration = $this->ask('How many days are you going fow');
        //$suitcase->methodOfTransport = $this->ask('');
        $suitcase->swimming = $this->ask('Are you planning to swim? 0 or 1');
        $suitcase->climate = $this->ask('What climate is there like, approx temp');
        $suitcase->generateList();

        $data = array();
        $headers = ['Main Items'];
        
        foreach ($suitcase->contents as $item) {

            $newLine =
                [
                    'Main Items' => $item
                ];
            $data[] = $newLine;

        }
        $this->table($headers, $data);

        $data = array();
        $headers = ['Kospital'];


        foreach ($suitcase->kospitalContents as $item) {

            $newLine =
                [
                    'Kospital' => $item
                ];
            $data[] = $newLine;

        }
        $this->table($headers, $data);

        $data = array();
        $headers = ['Optional'];

        foreach ($suitcase->optContents as $item) {

            $newLine =
                [
                    'Optional' => $item
                ];
            $data[] = $newLine;

        }

        $this->table($headers, $data);
    }
}
