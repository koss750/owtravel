<?php

namespace App\Console\Commands;

use App\Brodown;
use App\Document;
use App\DocumentTypes;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NewBrodown extends Command
{
    /**
     * The name and signature of the git addconsole command.
     *
     * @var string
     */
    protected $signature = 'new:brodown';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Brodown';

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
        $new = new Brodown;

        $new->name = $this->ask('Brodown Title, e.g. Brodown VII');
        $new->description = $this->ask('Brodown destination, e.g. Finland and Russia');

        $new->save();

        $this->info("Success, $new->name created");

    }
}
