<?php

namespace App\Console\Commands;

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
    protected $signature = 'new:document';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new document';

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

        $newDoc = new Document;
        $this->info("What type of document would you like to create?");
        $docTypes = DocumentTypes::all();
        foreach ($docTypes as $docType) {
            $this->info("<fg=cyan> $docType->id <fg=default;bg=black>$docType->description</>");
        }

        $docTypeIdQuery = $this->ask("ID: ");
        $docType = DocumentTypes::where('id', $docTypeIdQuery)->firstOrFail();
        $docTypeDesc = $docType->description;

        $newDoc = new Document;
        $this->info("Creating a $docTypeDesc for $user->name");

        $newDoc->number = $this->ask("<fg=cyan> $docTypeDesc number");
        $newDoc->valid_to = $this->ask("<fg=cyan> when will $docTypeDesc expire");
        $newDoc->issue_country = $this->ask("<fg=cyan> what country issued $docTypeDesc");
        $newDoc->description = $this->ask("<fg=cyan> give a short description to the $docTypeDesc");
        $newDoc->reference = Str::uuid();
        $newDoc->document_type_id = $docType->id;
        $newDoc->user_id = $user->id;
        $newDoc->document_link_type_id = 2;
        $newDoc->link = "no link yet";


        $newDoc->save();

        $this->info("Success");

    }
}
