<?php

namespace App\Console\Commands;

use App\Document;
use App\User;
use Illuminate\Console\Command;

class ShowDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:docs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows docs for a particular user';

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

        $docs = Document::where('user_id', $user->id)->get();
        $data = array();
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
