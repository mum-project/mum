<?php

namespace Tests\Feature\Console\Commands;

use App\SystemService;
use function factory;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Facades\App\Process;

class CheckSystemServicesHealthTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testBasicCheck()
    {
        $systemServices = factory(SystemService::class, 3)->create();
        $fakeSubState = $this->faker->word;

        $mockedSymfonyProcess = Mockery::mock();
        $mockedSymfonyProcess->shouldReceive('isSuccessful')
            ->andReturn(true);
        $mockedSymfonyProcess->shouldReceive('getOutput')
            ->andReturn('SubState=' . $fakeSubState);

        Process::shouldReceive('run')
            ->andReturn($mockedSymfonyProcess);

        $returnCode = $this->artisan('system-services:health');

        $this->assertEquals(0, $returnCode);

        $systemServices->each(function (SystemService $systemService) use ($fakeSubState) {
            $this->assertEquals($fakeSubState, $systemService->serviceHealthChecks()
                ->firstOrFail()->output);
        });
    }

    public function testFailingCheck()
    {
        /** @var SystemService $systemService */
        $systemService = factory(SystemService::class)->create();

        $mockedSymfonyProcess = Mockery::mock();
        $mockedSymfonyProcess->shouldReceive('isSuccessful')
            ->andReturn(false);
        $mockedSymfonyProcess->shouldReceive('getErrorOutput')
            ->andReturn('Look at me, I am a mocked error!');
        $mockedSymfonyProcess->shouldReceive('getExitCode')
            ->andReturn(1);

        Process::shouldReceive('run')
            ->andReturn($mockedSymfonyProcess);

        $returnCode = $this->artisan('system-services:health');

        $this->assertEquals(1, $returnCode);
        $this->assertTrue($systemService->serviceHealthChecks()
            ->doesntExist());
    }

    public function testDeleteOldChecksWithRunningState()
    {
        /** @var SystemService $systemService */
        $systemService = factory(SystemService::class)->create();

        $mockedSymfonyProcess = Mockery::mock();
        $mockedSymfonyProcess->shouldReceive('isSuccessful')
            ->andReturn(true);
        $mockedSymfonyProcess->shouldReceive('getOutput')
            ->andReturn('SubState=running');

        Process::shouldReceive('run')
            ->andReturn($mockedSymfonyProcess);

        for ($i = 0; $i < 3; $i++) {
            $returnCode = $this->artisan('system-services:health');
            $this->assertEquals(0, $returnCode);
            $this->assertEquals('running', $systemService->serviceHealthChecks()
                ->latest()
                ->firstOrFail()->output);
        }

        $this->assertEquals(1, $systemService->serviceHealthChecks()->count());
    }

    public function testSaveOldNotRunningChecks()
    {
        /** @var SystemService $systemService */
        $systemService = factory(SystemService::class)->create();

        $mockedSymfonyProcess = Mockery::mock();
        $mockedSymfonyProcess->shouldReceive('isSuccessful')
            ->andReturn(true);
        $mockedSymfonyProcess->shouldReceive('getOutput')
            ->andReturn('SubState=dead');

        Process::shouldReceive('run')
            ->andReturn($mockedSymfonyProcess);

        for ($i = 0; $i < 3; $i++) {
            $returnCode = $this->artisan('system-services:health');
            $this->assertEquals(0, $returnCode);
            $this->assertEquals('dead', $systemService->serviceHealthChecks()
                ->latest()
                ->firstOrFail()->output);
        }

        $this->assertEquals(3, $systemService->serviceHealthChecks()->count());
    }

    public function testDeleteOldestNotRunningChecks()
    {
        /** @var SystemService $systemService */
        $systemService = factory(SystemService::class)->create();

        $mockedSymfonyProcess = Mockery::mock();
        $mockedSymfonyProcess->shouldReceive('isSuccessful')
            ->andReturn(true);
        $mockedSymfonyProcess->shouldReceive('getOutput')
            ->andReturn('SubState=dead');

        Process::shouldReceive('run')
            ->andReturn($mockedSymfonyProcess);

        Config::set('mum.system_health.max_entries_incident_history', 2);

        for ($i = 0; $i < 3; $i++) {
            $returnCode = $this->artisan('system-services:health');
            $this->assertEquals(0, $returnCode);
            $this->assertEquals('dead', $systemService->serviceHealthChecks()
                ->latest()
                ->firstOrFail()->output);
        }

        $this->assertEquals(2, $systemService->serviceHealthChecks()->count());
    }
}
