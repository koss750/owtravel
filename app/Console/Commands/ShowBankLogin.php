<?php

namespace App\Console\Commands;

use App\BankCard;
use App\BankLogin;
use App\User;
use Illuminate\Console\Command;

class ShowBankLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:bank';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows user\'s bank logins';

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

            $items = BankLogin::where([
                ["user_id", "=", $user->id],
                ['bank', 'LIKE', "%$specificQuery%"]
            ])->get();

        }

        $headers = ['Bank', 'Login', 'Notes', 'Password 1 PGP', 'Password 2 PGP'];

        $data = array();

        foreach ($items as $item) {

            $data[] =
                [
                    'Bank' => $item->bank,
                    'Login' => $item->username,
                    'Notes' => $item->notes,
                    'Password 1 PGP' => $item->part1,
                    'Password 2 PGP' => $item->part2
                ];

        }

        $this->table($headers, $data);
    }
}
