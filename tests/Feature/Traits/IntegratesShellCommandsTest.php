<?php

namespace Tests\Feature\Traits;

use App\Exceptions\IntegrationsDisabledException;
use App\Exceptions\ShellCommandFailedException;
use App\Exceptions\ShellCommandUndefinedException;
use App\Interfaces\Integratable;
use App\ShellCommandIntegration;
use App\Traits\IntegratesShellCommands;
use Facades\App\Process;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use UnexpectedValueException;
use function zem_get_extension_info_by_id;

class IntegratesShellCommandsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @var IntegratesShellCommands */
    protected $integrator;

    /** @var Integratable */
    protected $integratable;

    protected function setUp()
    {
        parent::setUp();

        $this->integrator = new class {
            use IntegratesShellCommands;
        };

        $this->integratable = Mockery::mock(Integratable::class);
        $this->integratable->shouldReceive('getIntegratablePlaceholders')
            ->andReturn([
                'id'  => '42',
                'foo' => 'bar'
            ]);
        $this->integratable->shouldReceive('getIntegratableClassName')
            ->andReturn(Integratable::class);
    }

    public function testDisabledIntegrations()
    {
        Config::set('integrations.enabled.generally', false);
        Config::set('integrations.enabled.shell_commands', true);
        Config::set('integrations.shell_commands.01', 'foobar_command');

        $integration = factory(ShellCommandIntegration::class)->create([
            'event_type'  => 'created',
            'model_class' => $this->integratable->getIntegratableClassName(),
            'value'       => '01'
        ]);

        $this->expectException(IntegrationsDisabledException::class);

        $this->integrator->executeShellCommand($integration);
    }

    public function testDisabledShellCommands()
    {
        Config::set('integrations.enabled.generally', true);
        Config::set('integrations.enabled.shell_commands', false);
        Config::set('integrations.shell_commands.01', 'foobar_command');

        $integration = factory(ShellCommandIntegration::class)->create([
            'model_class' => $this->integratable->getIntegratableClassName(),
            'value'       => '01'
        ]);

        $this->expectException(IntegrationsDisabledException::class);

        $this->integrator->executeShellCommand($integration);
    }

    public function testBasicShellCommand()
    {
        $command = 'foobar_command';
        Config::set('integrations.enabled.generally', true);
        Config::set('integrations.enabled.shell_commands', true);
        Config::set('integrations.shell_commands.01', $command);

        $integration = factory(ShellCommandIntegration::class)->create([
            'model_class' => $this->integratable->getIntegratableClassName(),
            'value'       => '01'
        ]);

        $mockedSymfonyProcess = Mockery::mock();
        $mockedSymfonyProcess->shouldReceive('isSuccessful')
            ->andReturn(true);

        Process::shouldReceive('run')
            ->with($command)
            ->andReturn($mockedSymfonyProcess);

        $this->integrator->executeShellCommand($integration);
    }

    public function testFailingShellCommand()
    {
        $command = 'foobar_command';
        Config::set('integrations.enabled.generally', true);
        Config::set('integrations.enabled.shell_commands', true);
        Config::set('integrations.shell_commands.01', $command);

        $integration = factory(ShellCommandIntegration::class)->create([
            'model_class' => $this->integratable->getIntegratableClassName(),
            'value'       => '01'
        ]);

        $mockedSymfonyProcess = Mockery::mock();
        $mockedSymfonyProcess->shouldReceive('isSuccessful')
            ->andReturn(false);
        $errorOutput = 'Look at me, I am a mocked error!';
        $mockedSymfonyProcess->shouldReceive('getErrorOutput')
            ->andReturn($errorOutput);
        $mockedSymfonyProcess->shouldReceive('getExitCode')
            ->andReturn(1);

        Process::shouldReceive('run')
            ->with($command)
            ->andReturn($mockedSymfonyProcess);

        try {
            $this->integrator->executeShellCommand($integration);
        } catch (ShellCommandFailedException $e) {
            $this->assertEquals($errorOutput, $e->getErrorOutput());
            $this->assertEquals(1, $e->getExitCode());
        }
    }

    public function testShellParameters()
    {
        $command = 'foobar_command';
        Config::set('integrations.enabled.generally', true);
        Config::set('integrations.enabled.shell_commands', true);
        Config::set('integrations.options.shell_commands.allow_parameters', true);
        Config::set('integrations.shell_commands.01', $command);

        /** @var ShellCommandIntegration $integration */
        $integration = factory(ShellCommandIntegration::class)->create([
            'model_class' => $this->integratable->getIntegratableClassName(),
            'value'       => '01'
        ]);

        $integration->parameters()
            ->create([
                'option'         => '--foo',
                'value'          => '%{foo}',
                'use_equal_sign' => false
            ]);

        $integration->parameters()
            ->create([
                'option'         => '--id',
                'value'          => '%{id}',
                'use_equal_sign' => true
            ]);

        $mockedSymfonyProcess = Mockery::mock();
        $mockedSymfonyProcess->shouldReceive('isSuccessful')
            ->andReturn(true);

        Process::shouldReceive('run')
            ->with($command . ' --foo \'bar\' --id=\'42\'')
            ->once()
            ->andReturn($mockedSymfonyProcess);

        $this->integrator->executeShellCommand($integration, $this->integratable->getIntegratablePlaceholders());
    }

    public function testNoCommandSpecified()
    {
        Config::set('integrations.enabled.generally', true);
        Config::set('integrations.enabled.shell_commands', true);
        Config::set('integrations.options.shell_commands.allow_parameters', true);
        Config::set('integrations.shell_commands.01', null);

        DB::table('integrations')
            ->insert([
                'model_class' => $this->integratable->getIntegratableClassName(),
                'event_type'  => 'created',
                'type'        => ShellCommandIntegration::class,
                'value'       => '01'
            ]);

        $integration = ShellCommandIntegration::query()->firstOrFail();

        $this->expectException(ShellCommandUndefinedException::class);

        $this->integrator->executeShellCommand($integration);
    }
}
