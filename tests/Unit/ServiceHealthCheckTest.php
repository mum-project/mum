<?php

namespace Tests\Unit;

use App\ServiceHealthCheck;
use App\SystemService;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceHealthCheckTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemService::factory()->create();
    }

    public function testSystemService()
    {
        $systemService = SystemService::factory()->create();
        /** @var ServiceHealthCheck $serviceHealthCheck */
        $serviceHealthCheck = ServiceHealthCheck::factory()->create([
            'system_service_id' => $systemService->id,
            'output'            => 'foobar'
        ]);
        $this->assertTrue($systemService->is($serviceHealthCheck->systemService));
    }

    public function testWasRunning()
    {
        /** @var ServiceHealthCheck $serviceHealthCheck */
        $serviceHealthCheck = ServiceHealthCheck::factory()->create([
            'output'            => 'running'
        ]);
        $this->assertTrue($serviceHealthCheck->wasRunning());
    }

    public function testWasNotRunning()
    {
        /** @var ServiceHealthCheck $serviceHealthCheck */
        $serviceHealthCheck = ServiceHealthCheck::factory()->create([
            'output'            => 'dead'
        ]);
        $this->assertFalse($serviceHealthCheck->wasRunning());
    }
}
