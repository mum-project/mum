<?php

namespace Tests\Feature\Controllers;

use App\Alias;
use App\Domain;
use App\Exceptions\NotImplementedException;
use App\Http\Resources\IntegrationResource;
use App\Integration;
use App\Mailbox;
use App\ShellCommandIntegration;
use App\WebHookIntegration;
use function Clue\StreamFilter\fun;
use function compact;
use function csrf_token;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    private $admin;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
        factory(Mailbox::class)->create();
        factory(Alias::class)->create();
        $this->admin = factory(Mailbox::class)->create(['is_super_admin' => true]);

        Config::set('integrations.enabled.generally', true);
        Config::set('integrations.enabled.web_hooks', true);
        Config::set('integrations.enabled.shell_commands', true);
        Config::set('integrations.shell_commands.01', 'pwd');
        Config::set('integrations.shell_commands.02', null);
        Config::set('integrations.shell_commands.03', null);
        Config::set('integrations.shell_commands.04', null);
        Config::set('integrations.shell_commands.05', null);
        Config::set('integrations.shell_commands.06', null);
        Config::set('integrations.shell_commands.07', null);
        Config::set('integrations.shell_commands.08', null);
        Config::set('integrations.shell_commands.09', null);
        Config::set('integrations.shell_commands.10', null);
    }

    public function testIndex()
    {
        $perPage = (new ShellCommandIntegration())->getPerPage();

        /** @var Collection $shellCommandIntegrations */
        $shellCommandIntegrations = factory(ShellCommandIntegration::class, $perPage)->create();
        $webHookIntegrations = factory(WebHookIntegration::class, $perPage)->create();

        $guestResponsePage = $this->followingRedirects()
            ->get(route('integrations.index'));

        $responsePage1 = $this->actingAs($this->admin)
            ->get(route('integrations.index'))
            ->assertSuccessful();

        $responsePage2 = $this->actingAs($this->admin)
            ->get(route('integrations.index', ['page' => 2]))
            ->assertSuccessful();

        $shellCommandIntegrations->each(function (ShellCommandIntegration $integration) use (
            $guestResponsePage, $responsePage1, $responsePage2
        ) {
            $guestResponsePage->assertDontSee(route('integrations.show', compact('integration')));
            $responsePage1->assertSee(route('integrations.show', compact('integration')));
        });

        $webHookIntegrations->each(function (WebHookIntegration $integration) use (
            $guestResponsePage, $responsePage1, $responsePage2
        ) {
            $guestResponsePage->assertDontSee(route('integrations.show', compact('integration')));
            $responsePage2->assertSee(route('integrations.show', compact('integration')));
        });
    }

    public function testCreate()
    {
        Session::start();

        $this->actingAs($this->admin)
            ->get(route('integrations.create'))
            ->assertSuccessful();
    }

    public function testCreateIntegrationsDisabled()
    {
        Config::set('integrations.enabled.generally', false);

        $this->actingAs($this->admin)
            ->get(route('integrations.create'))
            ->assertNotFound();
    }

    public function testStoreShellCommandIntegration()
    {
        Session::start();

        $data = [
            'name'        => $this->faker->word,
            'model_class' => $this->faker->randomElement([
                'App\Mailbox',
                'App\Domain',
                'App\Alias'
            ]),
            'event_type'  => $this->faker->randomElement([
                'created',
                'updated',
                'deleted'
            ]),
            'type'        => 'shell_command',
            'value'       => '01',
            'parameters'  => [
                [
                    'option'         => $this->faker->word,
                    'value'          => $this->faker->randomElement([
                        '%{id}',
                        '%{domain}',
                        '%{description}',
                        '%{quota}',
                        '%{max_quota}',
                        '%{max_aliases}',
                        '%{max_mailboxes}',
                        '%{active}'
                    ]),
                    'use_equal_sign' => $this->faker->numberBetween(0, 1)
                ]
            ]
        ];

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->post(route('integrations.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful();

        $integration = Integration::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $this->assertTrue($integration->type === 'App\ShellCommandIntegration');
        $this->assertTrue($integration->value === $data['value']);
    }

    public function testStoreShellCommandIntegrationParamsDeactivated()
    {
        Config::set('integrations.options.shell_commands.allow_parameters', false);
        Session::start();

        $data = [
            'name'        => $this->faker->word,
            'model_class' => $this->faker->randomElement([
                Domain::class,
                Domain::class,
                Alias::class
            ]),
            'event_type'  => $this->faker->randomElement([
                'created',
                'updated',
                'deleted'
            ]),
            'type'        => 'shell_command',
            'value'       => '01',
            'parameters'  => [
                [
                    'option'         => $this->faker->word,
                    'value'          => $this->faker->randomElement([
                        '%{id}',
                        '%{domain}',
                        '%{description}',
                        '%{quota}',
                        '%{max_quota}',
                        '%{max_aliases}',
                        '%{max_mailboxes}',
                        '%{active}'
                    ]),
                    'use_equal_sign' => $this->faker->numberBetween(0, 1)
                ]
            ]
        ];

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->post(route('integrations.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful();

        /** @var ShellCommandIntegration $integration */
        $integration = ShellCommandIntegration::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $this->assertTrue($integration->value === $data['value']);
        $this->assertTrue($integration->parameters()
            ->doesntExist());
    }

    public function testStoreWebHookIntegration()
    {
        Session::start();

        $data = [
            'name'        => $this->faker->word,
            'model_class' => $this->faker->randomElement([
                'App\Mailbox',
                'App\Domain',
                'App\Alias'
            ]),
            'event_type'  => $this->faker->randomElement([
                'created',
                'updated',
                'deleted'
            ]),
            'type'        => 'web_hook',
            'value'       => $this->faker->url,
        ];

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->post(route('integrations.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful();

        $integration = Integration::query()
            ->where('name', $data['name'])
            ->firstOrFail();

        $this->assertTrue($integration->type === 'App\WebHookIntegration');
        $this->assertTrue($integration->value === $data['value']);
    }

    public function testStoreWithNoType()
    {
        Session::start();

        $data = [
            'name'        => $this->faker->word,
            'model_class' => $this->faker->randomElement([
                'App\Mailbox',
                'App\Domain',
                'App\Alias'
            ]),
            'event_type'  => $this->faker->randomElement([
                'created',
                'updated',
                'deleted'
            ]),
            'type'        => null,
            'value'       => $this->faker->url,
        ];

        $this->actingAs($this->admin)
            ->post(route('integrations.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertSessionHasErrors();
    }

    public function testStoreWithInvalidType()
    {
        Session::start();

        $data = [
            'name'        => $this->faker->word,
            'model_class' => $this->faker->randomElement([
                'App\Mailbox',
                'App\Domain',
                'App\Alias'
            ]),
            'event_type'  => $this->faker->randomElement([
                'created',
                'updated',
                'deleted'
            ]),
            'type'        => $this->faker->word,
            'value'       => $this->faker->url,
        ];

        $this->actingAs($this->admin)
            ->post(route('integrations.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertSessionHasErrors();
    }

    public function testShow()
    {
        $integration = factory(ShellCommandIntegration::class)->create([
            'name'  => $this->faker->word,
            'value' => '01'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('integrations.show', compact('integration')));
        $response->assertSuccessful();

        $response->assertSeeText($integration->name);
    }

    public function testEdit()
    {
        $integration = factory(ShellCommandIntegration::class)->create(['value' => '01']);
        $this->actingAs($this->admin)
            ->get(route('integrations.edit', compact('integration')))
            ->assertSuccessful();

        $integration = factory(WebHookIntegration::class)->create();
        $this->actingAs($this->admin)
            ->get(route('integrations.edit', compact('integration')))
            ->assertSuccessful();
    }

    public function testUpdateShellCommandIntegration()
    {
        Config::set('integrations.shell_commands.02', 'cd');

        Session::start();

        $integration = factory(ShellCommandIntegration::class)->create([
            'name'  => $this->faker->word,
            'value' => '01'
        ]);
        $data = [
            'name'  => $this->faker->word,
            'value' => '02',
        ];

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->patch(
                route('integrations.update', compact('integration')),
                array_merge($data, ['_token' => csrf_token()])
            )
            ->assertSuccessful();

        $integration = Integration::query()
            ->where('name', $data['name'])
            ->where('type', $integration->type)
            ->firstOrFail();

        $this->assertTrue($integration->value === $data['value']);
    }

    public function testUpdateWebHookIntegration()
    {
        Session::start();

        $integration = factory(WebHookIntegration::class)->create(['name' => $this->faker->word]);
        $data = [
            'name'  => $this->faker->word,
            'value' => $this->faker->url,
        ];

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->patch(
                route('integrations.update', compact('integration')),
                array_merge($data, ['_token' => csrf_token()])
            )
            ->assertSuccessful();

        $integration = Integration::query()
            ->where('name', $data['name'])
            ->where('type', $integration->type)
            ->firstOrFail();

        $this->assertTrue($integration->value === $data['value']);
    }

    public function testUpdateNotImplementedIntegrationType()
    {
        $integration = Integration::create([
            'type'        => 'foo',
            'value'       => 'bar',
            'model_class' => Mailbox::class,
            'event_type'  => 'created'
        ]);

        Session::start();

        $data = [
            'model_class' => Domain::class
        ];

        $this->withoutExceptionHandling();
        $this->expectException(NotImplementedException::class);

        $this->actingAs($this->admin)
            ->patch(
                route('integrations.update', compact('integration')),
                array_merge($data, ['_token' => csrf_token()])
            );
    }

    public function testDestroy()
    {
        Session::start();

        $integration = factory(WebHookIntegration::class)->create();
        $this->followingRedirects()
            ->actingAs($this->admin)
            ->delete(route('integrations.destroy', compact('integration')), ['_token' => csrf_token()])
            ->assertSuccessful();
        $this->assertDatabaseMissing('integrations', $integration->toArray());
    }
}
