<?php

namespace Tests\Unit;

use App\Integration;
use App\IntegrationParameter;
use App\ShellCommandIntegration;
use function factory;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationParameterTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('integrations.enabled.generally', true);
        Config::set('integrations.enabled.shell_commands', true);
        Config::set('integrations.shell_commands.01', 'pwd');
    }

    public function testIntegration()
    {
        $integration = ShellCommandIntegration::factory()->create(['value' => '01']);
        $integrationParameter = $integration->parameters()
            ->create([
                'option' => '--foo',
                'value'  => 'bar'
            ]);
        $this->assertTrue($integration->is($integrationParameter->integration));
    }

    public function testGetParameterString()
    {
        $integration = ShellCommandIntegration::factory()->create(['value' => '01']);
        $integrationParameter1 = $integration->parameters()
            ->create([
                'option' => '--foo',
                'value'  => 'bar'
            ]);
        $integrationParameter2 = $integration->parameters()
            ->create([
                'option' => '--foo',
                'value' => 'bar',
                'use_equal_sign' => true
            ]);
        $integrationParameter3 = $integration->parameters()
            ->create([
                'option' => null,
                'value' => 'foo'
            ]);
        $this->assertEquals('--foo \'bar\'', $integrationParameter1->getParameterString());
        $this->assertEquals('--foo=\'bar\'', $integrationParameter2->getParameterString());
        $this->assertEquals('\'foo\'', $integrationParameter3->getParameterString());
    }
}
