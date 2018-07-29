<?php

namespace App\Console\Commands;

use App\Domain;
use Illuminate\Console\Command;

class DomainsCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domains:create 
                            {domain : Domain to create, eg. example.com}
                            {--description=}
                            {--quota=}
                            {--max_quota=}
                            {--max_aliases=}
                            {--max_mailboxes=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new domain. ATTENTION: This command currently does not perform ANY validation of supplied data.';

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
        $data = [
            'domain'        => $this->argument('domain'),
            'description'   => $this->option('description'),
            'quota'         => $this->option('quota'),
            'max_quota'     => $this->option('max_quota'),
            'max_aliases'   => $this->option('max_aliases'),
            'max_mailboxes' => $this->option('max_mailboxes'),
        ];
        $domain = Domain::create($data);
        $this->info($domain->domain . ' was created successfully.', 1);
        return 0;
    }
}
