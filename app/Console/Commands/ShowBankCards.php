<?php

namespace App\Console\Commands;

use App\BankCard;
use App\User;
use Illuminate\Console\Command;

class ShowBankCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows user\'s bank cards';

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
            $this->info("Which of these users did you mean?");
            foreach ($user as $item) {
                $this->info("<fg=cyan> $item->id    <fg=default;bg=black>$item->name</>");
            }
            $userQuery = $this->ask("ID:");
            $user = User::where('id', $userQuery)->firstOrFail();
        }
        else $user = User::where('name', 'LIKE', '%' .$userQuery.'%')->firstOrFail();

        $specificQuery = $this->ask("Which bank? Type 'all' for all cards");

        if ($specificQuery == "all") {

            $items = BankCard::where('user_id', $user->id)->get();

        }

        else {

            $items = BankCard::where([
                ["user_id", "=", $user->id],
                ['bank', 'LIKE', "%$specificQuery%"]
            ])->get();

        }

        $headers = ['Bank', 'Number', 'Expiry', 'CVC'];

        $data = array();

        foreach ($items as $item) {

            $data[] =
                [
                    'Bank' => $item->bank,
                    'Number' => $item->ln,
                    'Expiry' => "$item->expiry_month / $item->expiry_year",
                    'CVC' => $item->CVC
                ];

        }

        $this->table($headers, $data);
    }
}
