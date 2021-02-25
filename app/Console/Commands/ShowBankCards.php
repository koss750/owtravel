<?php

namespace App\Console\Commands;

use App\BankCard;
use App\User;
use DecryptException;
use Illuminate\Console\Command;

class ShowBankCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:payment {name} {--curve} {--amex} {--debug} {--bank=} {--e=}';

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

        $userQuery = $this->argument("name");
        $globalSearch = false;

        if ($this->argument("name")=="xx") $userQuery = "konstantin";

        if ($this->argument("name")=="x") $globalSearch = true;
        else {
            $user = User::where('name', 'LIKE', '%' . $userQuery . '%')->get();
            if ($user->count() > 1) {
                $user->toArray();
                $this->info("Which of these users did you mean?");
                foreach ($user as $item) {
                    $this->info("<fg=cyan> $item->id    <fg=default;bg=black>$item->name</>");
                }
                $userQuery = $this->ask("ID:");
                $user = User::where('id', $userQuery)->firstOrFail();
            } else $user = User::where('name', 'LIKE', '%' . $userQuery . '%')->firstOrFail();
        }

        if ($this->option('e')) {
            $likeQuery = $this->option('e');
            $globalSearch = true;
        }

        if ($globalSearch) {
            $items = BankCard::all();
        }
        else {

            if ($this->option('amex')) $specificQuery = "amex";
            else if ($this->option('curve')) $specificQuery = "curve";
            else if ($this->option('bank')) $specificQuery = $this->option('bank');
            else $specificQuery = $this->ask("Which bank? Type 'all' for all cards");

            if ($specificQuery == "all") {

            $items = BankCard::where('user_id', $user->id)->get();

        } else {

                $items = BankCard::where([
                    ["user_id", "=", $user->id],
                    ['bank', 'LIKE', "%$specificQuery%"]
                ])->get();
            }
        }

        $headers = ['Holder', 'Bank', 'Account', 'Number', 'Expiry', 'CVC'];
        if (!$globalSearch) unset($headers[0]);

        $lastProcessedUser = null;
        $cardholderName = "A CARDHOLDER";
        $data = array();
        $counter = 0;

        try {
            foreach ($items as $item) {

                $counter++;

                $addRow = false;
                $lastProcessedUser = $item->user_id;

                if (isset($likeQuery)) {
                    if ($item->last_four == $likeQuery) {
                        $addRow = true;
                    }
                }
                else $addRow = true;

                if ($globalSearch) {
                    $cardholder = User::where('id', $item->user_id)->firstOrFail();
                    $cardholderName = $cardholder->name;
                }
                else if (isset($user)) $cardholderName = $user->name;
                else $cardholderName = "error getting name";

                if ($counter==1) {
                    $cardForTextArt = $item;
                    $cardholderNameForTextArt = $user->name;
                }

                if ($addRow) {
                    $newRow = [
                        'Holder' => $cardholderName,
                        'Bank' => $item->bank,
                        'Account' => $item->account,
                        'Number' => decrypt($item->ln),
                        'Expiry' => "$item->expiry_month / $item->expiry_year",
                        'CVC' => decrypt($item->CVC)
                    ];

                    if (!$globalSearch) unset($newRow["Holder"]);
                    $data[] = $newRow;

                }

            }
            if (isset($cardForTextArt)) {
                $firstCardBank = $cardForTextArt->bank;
                $firstCardAccount = $cardForTextArt->account;
                $firstCardNumber = $cardForTextArt->ln;
                $firstCardNumber = $cardForTextArt->formatLongNumber($firstCardNumber);
                $firstCardExpiry = "$cardForTextArt->expiry_month / $cardForTextArt->expiry_year";
                $firstCardCVC = $cardForTextArt->CVC;
                $firstCardHolder = $cardholderNameForTextArt ?? $cardholderName;
                $textArt = "___________________________________
                |#######====================#######|
                |#*$firstCardBank                   *#|
                |#**$firstCardAccount /===\             **#|
                |#  $firstCardNumber           #|
                |#*          | /v\ |             *#|
                |#exp $firstCardExpiry   cvv $firstCardCVC          (1)#|
                |#$firstCardHolder===========*VISA*#|
                ------------------------------------
                ";
                $this->info($textArt);
            }
            $this->table($headers, $data);
        } catch (\Exception $e) {
            if (!isset($user)) {
                $user = User::where('id', $lastProcessedUser)->first();
            }
            $this->warn("One or more cards for $user->name is not encrypted. Please run 'link:encrypt:cards $user->id' and try again");
            $this->warn("If that is not the case and all cards are safe, please refer to the following error:");
            $this->warn($e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
        }

    }
}
