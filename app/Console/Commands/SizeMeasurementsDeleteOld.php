<?php

namespace App\Console\Commands;

use App\SizeMeasurement;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SizeMeasurementsDeleteOld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'size-measurements:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all size measurements older than the maximum time specified in the configuration.';

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
        if (!config('mum.size_measurements.delete_old')) {
            $this->error('Deleting old size measurements is disabled, aborting...');
            return 1;
        }
        $this->deleteOldSizeMeasurements();
        return 0;
    }

    /**
     * Deletes all size measurements that are older than the maximum age.
     */
    protected function deleteOldSizeMeasurements()
    {
        $maximumMonths = config('mum.size_measurements.delete_after.months');
        $maximumWeeks = config('mum.size_measurements.delete_after.weeks');
        $maximumDays = config('mum.size_measurements.delete_after.days');

        $maximumTimestamp = Carbon::now()
            ->subMonths($maximumMonths)
            ->subWeeks($maximumWeeks)
            ->subDays($maximumDays);

        SizeMeasurement::query()
            ->where('created_at', '<', $maximumTimestamp)
            ->delete();
    }
}
