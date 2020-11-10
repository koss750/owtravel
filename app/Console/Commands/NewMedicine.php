<?php

namespace App\Console\Commands;

use App\Country;
use App\Document;
use App\DocumentTypes;
use App\Kospital;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NewMedicine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'new:medicine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Kospital item';

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
        $this->info("Creating a new Kospital Item");
        $item = new Kospital;
        $item->code = $this->ask("Code:");
        $item->name = $this->ask("Name");
        $item->default_doze = $this->ask("Dose (without units)");
        $item->dd_units = $this->ask("Dose units");
        $item->default_origin = $this->ask("Default origin country code");
        $country = Country::where('iso_3', $item->default_origin)->firstOrFail();
        $country = $country->title;
        $item->otc = $this->ask("Is this over-the-counter in $country?");
        $item->cd = $this->ask("Is this a controlled drug in $country?");
        $item->pom = $this->ask("Is this prescription-only in $country?");
        $item->supplement = $this->ask("Is this a food supplement?");
        $item->reference = Str::uuid();
        $item->save();

        $this->info("Success");

    }
}
