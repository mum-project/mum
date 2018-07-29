<?php

namespace Tests\Feature\Console\Commands;

use App\Domain;
use App\SizeMeasurement;
use Carbon\Carbon;
use function factory;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SizeMeasurementsDeleteOldTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testDeleteOldSizeMeasurements()
    {
        Config::set('mum.size_measurements.delete_old', true);
        Config::set('mum.size_measurements.delete_after.months', 1);
        Config::set('mum.size_measurements.delete_after.weeks', 1);
        Config::set('mum.size_measurements.delete_after.days', 1);

        /** @var Domain $domain */
        $domain = factory(Domain::class)->create();
        $this->createSizeMeasurements($domain);

        $this->assertTrue($domain->sizeMeasurements()
                ->count() === 4);

        $returnCode = $this->artisan('size-measurements:delete-old');
        $this->assertTrue($returnCode === 0);

        $this->assertTrue($domain->sizeMeasurements()
                ->count() === 2);
        $domain->sizeMeasurements()
            ->each(function (SizeMeasurement $measurement) {
                $this->assertTrue($measurement->created_at > Carbon::now()
                        ->subMonths(1)
                        ->subWeeks(1)
                        ->subDays(1));
            });
    }

    public function testDeactivated()
    {
        Config::set('mum.size_measurements.delete_old', false);

        /** @var Domain $domain */
        $domain = factory(Domain::class)->create();
        $this->createSizeMeasurements($domain);

        $this->assertTrue($domain->sizeMeasurements()
                ->count() === 4);

        $returnCode = $this->artisan('size-measurements:delete-old');
        $this->assertTrue($returnCode === 1);

        $this->assertTrue($domain->sizeMeasurements()
                ->count() === 4);
    }

    private function createSizeMeasurements(Domain $domain)
    {
        $domain->sizeMeasurements()
            ->create([
                'size'       => $this->faker->numberBetween(100, 1000),
                'created_at' => Carbon::now()
                    ->subMonths(2)
            ]);

        $domain->sizeMeasurements()
            ->create([
                'size'       => $this->faker->numberBetween(100, 1000),
                'created_at' => Carbon::now()
                    ->subMonths(1)
                    ->subWeeks(1)
                    ->subDays(2)
            ]);


        $domain->sizeMeasurements()
            ->create([
                'size'       => $this->faker->numberBetween(100, 1000),
                'created_at' => Carbon::now()
                    ->subMonths(1)
                    ->subWeeks(1)
            ]);

        $domain->sizeMeasurements()
            ->create([
                'size'       => $this->faker->numberBetween(100, 1000),
                'created_at' => Carbon::now()
            ]);
    }
}
