<?php

namespace App\Console\Commands;

use App\BankCard;
use App\Document;
use App\DocumentTypes;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NewDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'new:card';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new bank card';

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
        $userQuery = $this->ask('Full name of the user');


        $user = User::where('name', 'LIKE', '%' .$userQuery.'%')->get();
        if ($user->count()>1) {
            $user->toArray();
            $this->info("Select the ID of the user");
            foreach ($user as $item) {
                $this->info("<fg=cyan> $item->id    <fg=default;bg=black>$item->name</>");
            }
            $userQuery = $this->ask("ID:");
            $user = User::where('id', $userQuery)->firstOrFail();
        }

        else $user = User::where('name', 'LIKE', '%' .$userQuery.'%')->firstOrFail();

        $newCard = new BankCard;
        $newCard->user_id=$user->id;
        $newCard->bank  = $this->ask("Bank or card name: ");
        $newCard->ln  = $this->ask("Long Cumber: ");
        $newCard->expiry_month  = $this->ask("ExpiryCmonth: ");
        $newCard->expiry_year  = $this->ask("ExpirC year: ");
        $newCard->CVC  = $this->ask("CVV or CVC code: ");
        $this->info("Adding $newCard->bank for $user->name");
        $newCard->reference = Str::uuid();
        $newCard->save();

        $this->info("Success");

    }
}
