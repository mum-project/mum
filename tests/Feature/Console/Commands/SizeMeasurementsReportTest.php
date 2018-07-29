<?php

namespace Tests\Feature\Console\Commands;

use App\Domain;
use App\Mailbox;
use App\SizeMeasurement;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SizeMeasurementsReportTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
    }

    public function testReportDefaultSize()
    {
        /** @var Mailbox $mailbox */
        $mailbox = factory(Mailbox::class)->create();
        $size = $this->faker->numberBetween(100, 10000);

        $this->assertTrue($mailbox->sizeMeasurements()
            ->doesntExist());

        $this->artisan('size-measurements:report', [
            'directory' => $mailbox->homedir,
            'size'      => $size
        ]);

        $this->assertEquals($size, $mailbox->sizeMeasurements()
            ->firstOrFail()->size);
    }

    public function testReportInKiB()
    {
        /** @var Mailbox $mailbox */
        $mailbox = factory(Mailbox::class)->create();
        $size = $this->faker->numberBetween(100, 10000);

        $this->assertTrue($mailbox->sizeMeasurements()
            ->doesntExist());

        $this->artisan('size-measurements:report', [
            'directory' => $mailbox->homedir,
            'size'      => $size,
            '--KiB'     => true
        ]);

        $this->assertEquals($size, $mailbox->sizeMeasurements()
            ->firstOrFail()->size);
    }

    public function testReportInMiB()
    {
        /** @var Mailbox $mailbox */
        $mailbox = factory(Mailbox::class)->create();
        $size = $this->faker->numberBetween(100, 10000);

        $this->assertTrue($mailbox->sizeMeasurements()
            ->doesntExist());

        $this->artisan('size-measurements:report', [
            'directory' => $mailbox->homedir,
            'size'      => $size,
            '--MiB'     => true
        ]);

        $sizeInMiB = $size * 1024;

        $this->assertEquals($sizeInMiB, $mailbox->sizeMeasurements()
            ->firstOrFail()->size);
    }

    public function testReportInGiB()
    {
        /** @var Mailbox $mailbox */
        $mailbox = factory(Mailbox::class)->create();
        $size = $this->faker->numberBetween(100, 10000);

        $this->assertTrue($mailbox->sizeMeasurements()
            ->doesntExist());

        $this->artisan('size-measurements:report', [
            'directory' => $mailbox->homedir,
            'size'      => $size,
            '--GiB'     => true
        ]);

        $sizeInGiB = $size * 1024 * 1024;

        $this->assertEquals($sizeInGiB, $mailbox->sizeMeasurements()
            ->firstOrFail()->size);
    }

    public function testReportOnDomain()
    {
        /** @var Domain $mailbox */
        $domain = factory(Domain::class)->create();
        $size = $this->faker->numberBetween(100, 10000);

        $this->assertTrue($domain->sizeMeasurements()
            ->doesntExist());

        $this->artisan('size-measurements:report', [
            'directory' => $domain->homedir,
            'size'      => $size
        ]);

        $this->assertEquals($size, $domain->sizeMeasurements()
            ->firstOrFail()->size);
    }

    public function testReportOnRootFolder()
    {
        $rootHomedir = '/srv/mail/mailboxes';
        Config::set('mum.mailboxes.root_directory', $rootHomedir);
        $size = $this->faker->numberBetween(100, 10000);

        $this->assertTrue(SizeMeasurement::query()
            ->ofRootFolder()
            ->doesntExist());

        $this->artisan('size-measurements:report', [
            'directory' => $rootHomedir,
            'size'      => $size
        ]);

        $this->assertEquals($size, SizeMeasurement::query()
            ->ofRootFolder()
            ->firstOrFail()->size);
    }

    public function testConflictingOptions()
    {
        /** @var Mailbox $mailbox */
        $mailbox = factory(Mailbox::class)->create();
        $size = $this->faker->numberBetween(100, 10000);

        $this->assertTrue($mailbox->sizeMeasurements()
            ->doesntExist());

        $returnCode = $this->artisan('size-measurements:report', [
            'directory' => $mailbox->homedir,
            'size'      => $size,
            '--MiB'     => true,
            '--GiB'     => true
        ]);

        $this->assertEquals(1, $returnCode);

        $this->assertTrue($mailbox->sizeMeasurements()
            ->doesntExist());
    }

    public function testInvalidHomedir()
    {
        $size = $this->faker->numberBetween(100, 10000);

        $returnCode = $this->artisan('size-measurements:report', [
            'directory' => '/some/nonexistent/mailbox',
            'size'      => $size
        ]);

        $this->assertEquals(1, $returnCode);
        $this->assertEquals(0, SizeMeasurement::query()
            ->count());
    }

    public function testInvalidSize()
    {
        /** @var Mailbox $mailbox */
        $mailbox = factory(Mailbox::class)->create();
        $this->assertTrue($mailbox->sizeMeasurements()->doesntExist());

        $returnCode = $this->artisan('size-measurements:report', [
            'directory' => $mailbox->homedir,
            'size'      => 'NOT-A-NUMBER'
        ]);

        $this->assertEquals(1, $returnCode);
        $this->assertTrue($mailbox->sizeMeasurements()->doesntExist());
    }
}
