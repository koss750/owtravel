<?php

namespace App\Console\Commands;

use App\BankCard;
use App\User;
use Illuminate\Console\Command;

class EncryptBankCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:encrypt:cards {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt user\'s bank cards';

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
        $headers = ['Bank', 'Status'];
        $data = array();

        if ($userQuery == "all") {
            $items = BankCard::all();
        }
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
            } else $user = User::where('name', 'LIKE', '%' . $userQuery . '%')->orWhere('id', $userQuery)->firstOrFail();

            $items = BankCard::where('user_id', $user->id)->get();
        }

        foreach ($items as $card) {
            try {
                $card->ln = encrypt($card->ln);
                $card->CVC = encrypt($card->CVC);
                $card->save();
                $data[] =
                    [
                        'Bank' => $card->bank,
                        'Status' => "Encrypted - OK"
                ];
            } catch (\Exception $e) {
                $this->warn ("$card->bank card likely is already encrypted, skipping");
                $data[] =
                    [
                        'Bank' => $card->bank,
                        'Status' => "Skipped"
                    ];
            }
        }


        $this->table($headers, $data);
    }
}
