<?php

namespace App\Console\Commands;

use App\Alias;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeactivateAliases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aliases:deactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command deactivates all aliases where the deactivate_at field contains a timestamp from the past.';

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
        $aliasesToDeactivate = Alias::query()
            ->where('deactivate_at', '<', Carbon::now());

        $aliasesToDeactivate->each(function (Alias $alias) {
            $alias->update(['active' => false]);
        });

        return 0;
    }
}
