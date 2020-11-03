<?php

namespace App\Console\Commands;

use App\Country;
use App\Document;
use App\DocumentTypes;
use App\Kospital;
use App\User;
use Illuminate\Console\Command;

class ShowMedicine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:meds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows Kospital records';

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
        $query = $this->ask('Enter the code or name of medication. For a full list type all');

        $items = Kospital::where('code', $query)->orWhere("name", "LIKE", "%$query%")->orderBy('code', 'DESC')->get();
        $data = array();
        $headers = ['Code', 'Name', 'Dose', 'Notes'];
        
        foreach ($items as $item) {

            $dose = $item->default_dose . $item->dd_unit;
            $newLine =
                [
                    'Code' => $item->code,
                    'Name' => $item->name,
                    'Dose' => $dose,
                ];
            if ($item->cd || $item->otc || $item->supplement) {

                $country = Country::where('iso_3', $item->default_origin)->firstOrFail();
                $country = $country->title;
                if ($item->cd) {
                    $newLine['Notes'] = "Controlled Drug in origin ($country)";
                }
                if ($item->otc) {
                    $newLine['Notes'] = "Over-the-counter in origin ($country)";
                }
                if ($item->supplement) {
                    $newLine['Notes'] = "Food supplement, not a medicine";
                }
                
            }
            $data[] = $newLine;

        }

        $this->table($headers, $data);
    }
}
