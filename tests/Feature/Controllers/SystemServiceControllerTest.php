<?php

namespace Tests\Feature\Controllers;

use App\Domain;
use App\Http\Resources\SystemServiceResource;
use App\Mailbox;
use App\SystemService;
use function array_merge;
use function compact;
use function csrf_token;
use function factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemServiceControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @var Mailbox */
    protected $admin;

    /** @var Mailbox */
    protected $mailbox;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('mum.system_health.check_services', true);
        Domain::factory()->create();
        $this->admin = Mailbox::factory()->create(['is_super_admin' => true]);
        $this->mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
    }

    public function testIndex()
    {
        $perPage = (new SystemService)->getPerPage();
        /** @var Collection $systemServices1 */
        $systemServices1 = SystemService::factory( $perPage)->create();
        $systemServices2 = SystemService::factory( $perPage)->create();

        $this->get(route('system-services.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->get(route('system-services.index'))
            ->assertStatus(403);

        $responseData1 = $this->actingAs($this->admin)
                             ->get(route('system-services.index'))
                             ->assertSuccessful()
                             ->getOriginalContent()
                             ->getData()['systemServices'];

        $responseData2 = $this->actingAs($this->admin)
                             ->get(route('system-services.index', ['page' => 2]))
                             ->assertSuccessful()
                             ->getOriginalContent()
                             ->getData()['systemServices'];

        $systemServices1->each(function (SystemService $systemService) use ($responseData1, $responseData2) {
            $this->assertTrue($responseData1->contains($systemService));
            $this->assertFalse($responseData2->contains($systemService));
        });

        $systemServices2->each(function (SystemService $systemService) use ($responseData1, $responseData2) {
            $this->assertFalse($responseData1->contains($systemService));
            $this->assertTrue($responseData2->contains($systemService));
        });
    }

    public function testIndexJson()
    {
        $perPage = (new SystemService)->getPerPage();
        /** @var Collection $systemServices1 */
        $systemServices1 = SystemService::factory( $perPage)->create();
        $systemServices2 = SystemService::factory( $perPage)->create();

        $this->getJson(route('system-services.index'))
            ->assertStatus(401);

        $this->actingAs($this->mailbox)
            ->getJson(route('system-services.index'))
            ->assertStatus(403);

        $response1 = $this->actingAs($this->admin)
            ->getJson(route('system-services.index'))
            ->assertSuccessful();

        $response2 = $this->actingAs($this->admin)
            ->getJson(route('system-services.index', ['page' => 2]))
            ->assertSuccessful();

        $systemServices1->each(function (SystemService $systemService) use ($response1, $response2) {
            $response1->assertJsonFragment((new SystemServiceResource($systemService))->jsonSerialize());
            $response2->assertJsonMissingExact((new SystemServiceResource($systemService))->jsonSerialize());
        });

        $systemServices2->each(function (SystemService $systemService) use ($response1, $response2) {
            $response1->assertJsonMissingExact((new SystemServiceResource($systemService))->jsonSerialize());
            $response2->assertJsonFragment((new SystemServiceResource($systemService))->jsonSerialize());
        });
    }

    public function testCreate()
    {
        $this->get(route('system-services.create'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->get(route('system-services.create'))
            ->assertStatus(403);

        $this->actingAs($this->admin)
            ->get(route('system-services.create'))
            ->assertSuccessful();
    }

    public function testStore()
    {
        Session::start();

        $data = [
            'service' => $this->faker->word,
            'name'    => $this->faker->word
        ];

        $this->post(route('system-services.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->post(route('system-services.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(403);

        $this->assertDatabaseMissing('system_services', $data);

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->post(route('system-services.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful();

        $this->assertDatabaseHas('system_services', $data);
    }

    public function testShow()
    {
        /** @var SystemService $systemService */
        $systemService = SystemService::factory()->create();

        $this->get(route('system-services.show', compact('systemService')))
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->get(route('system-services.show', compact('systemService')))
            ->assertStatus(403);

        $responseData = $this->actingAs($this->admin)
                            ->get(route('system-services.show', compact('systemService')))
                            ->assertSuccessful()
                            ->getOriginalContent()
                            ->getData()['systemService'];

        $this->assertTrue($systemService->is($responseData));
    }

    public function testEdit()
    {
        $systemService = SystemService::factory()->create();

        $this->get(route('system-services.edit', compact('systemService')))
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->get(route('system-services.edit', compact('systemService')))
            ->assertStatus(403);

        $this->actingAs($this->admin)
            ->get(route('system-services.edit', compact('systemService')))
            ->assertSuccessful();
    }

    public function testUpdate()
    {
        Session::start();

        $systemService = SystemService::factory()->create();
        $data = [
            'service' => 'my-updated-service',
            'name'    => 'My Updated Service'
        ];

        $this->patch(
            route('system-services.update', compact('systemService')),
            array_merge($data, ['_token' => csrf_token()])
        )
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->patch(
                route('system-services.update', compact('systemService')),
                array_merge($data, ['_token' => csrf_token()])
            )
            ->assertStatus(403);

        $this->assertDatabaseMissing('system_services', $data);

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->patch(
                route('system-services.update', compact('systemService')),
                array_merge($data, ['_token' => csrf_token()])
            )
            ->assertSuccessful();

        $this->assertDatabaseHas('system_services', $data);
    }

    public function testDestroy()
    {
        Session::start();
        /** @var SystemService $systemService */
        $systemService = SystemService::factory()->create();

        $this->delete(route('system-services.destroy', compact('systemService')), ['_token' => csrf_token()])
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->delete(route('system-services.destroy', compact('systemService')), ['_token' => csrf_token()])
            ->assertStatus(403);

        $this->assertDatabaseHas('system_services', $systemService->only([
            'id',
            'service',
            'name'
        ]));

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->delete(route('system-services.destroy', compact('systemService')), ['_token' => csrf_token()])
            ->assertSuccessful();

        $this->assertDatabaseMissing('system_services', $systemService->only([
            'id',
            'service',
            'name'
        ]));
    }

    public function testDisabledCheckServices()
    {
        Session::start();
        Config::set('mum.system_health.check_services', false);
        $systemService = SystemService::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('system-services.index'))
            ->assertForbidden();
        $this->actingAs($this->admin)
            ->get(route('system-services.create'))
            ->assertForbidden();
        $this->actingAs($this->admin)
            ->post(route('system-services.store'), ['_token' => csrf_token()])
            ->assertForbidden();
        $this->actingAs($this->admin)
            ->get(route('system-services.edit', compact('systemService')))
            ->assertForbidden();
        $this->actingAs($this->admin)
            ->patch(route('system-services.update', compact('systemService')), ['_token' => csrf_token()])
            ->assertForbidden();
        $this->actingAs($this->admin)
            ->delete(route('system-services.destroy', compact('systemService')), ['_token' => csrf_token()])
            ->assertForbidden();
    }
}
