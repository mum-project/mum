<?php

namespace Tests\Feature\Controllers;

use App\Domain;
use App\Mailbox;
use App\TlsPolicy;
use function array_merge;
use function compact;
use function csrf_token;
use function factory;
use Illuminate\Support\Facades\Session;
use function route;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TlsPolicyControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
    }

    public function testIndex()
    {
        $admin = factory(Mailbox::class)->create(['is_super_admin' => true, 'active' => true]);
        $user = factory(Mailbox::class)->create(['is_super_admin' => false, 'active' => true]);
        $perPage = (new TlsPolicy())->getPerPage();
        factory(TlsPolicy::class, $perPage * 2)->create();

        $tlsPolicies1 = TlsPolicy::orderBy('domain', 'asc')
            ->take($perPage)
            ->get();
        $tlsPolicies2 = TlsPolicy::orderBy('domain', 'asc')
            ->skip($perPage)
            ->take($perPage)
            ->get();

        $this->get(route('tls-policies.index'))
            ->assertStatus(302);
        $this->actingAs($user)
            ->get(route('tls-policies.index'))
            ->assertStatus(403);

        $responsePage1 = $this->actingAs($admin)
            ->get(route('tls-policies.index'));
        $responsePage2 = $this->actingAs($admin)
            ->get(route('tls-policies.index', ['page' => 2]));

        $responsePage1->assertSuccessful();
        $responsePage2->assertSuccessful();

        $tlsPolicies1->each(function (TlsPolicy $tlsPolicy) use ($responsePage1, $responsePage2) {
            $responsePage1->assertSee("href=\"" . route('tls-policies.show', compact('tlsPolicy')) . "\"");
            $responsePage2->assertDontSee("href=\"" . route('tls-policies.show', compact('tlsPolicy')) . "\"");
        });
        $tlsPolicies2->each(function (TlsPolicy $tlsPolicy) use ($responsePage1, $responsePage2) {
            $responsePage1->assertDontSee("href=\"" . route('tls-policies.show', compact('tlsPolicy')) . "\"");
            $responsePage2->assertSee("href=\"" . route('tls-policies.show', compact('tlsPolicy')) . "\"");
        });
    }

    public function testCreate()
    {
        $admin = factory(Mailbox::class)->create(['is_super_admin' => true, 'active' => true]);
        $user = factory(Mailbox::class)->create(['is_super_admin' => false, 'active' => true]);

        $this->get(route('tls-policies.create'))
            ->assertStatus(302);

        $this->actingAs($user)
            ->get(route('tls-policies.create'))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->get(route('tls-policies.create'))
            ->assertSuccessful()
            ->assertSee(route('tls-policies.store'));
    }

    public function testStore()
    {
        $admin = factory(Mailbox::class)->create(['is_super_admin' => true]);
        $user = factory(Mailbox::class)->create(['is_super_admin' => false]);

        $data = [
            'domain'      => $this->faker->unique()->domainName,
            'policy'      => $this->faker->randomElement([
                'none',
                'may',
                'encrypt',
                'dane',
                'dane-only',
                'verify',
                'secure'
            ]),
            'params'      => 'match=.example.com',
            'description' => $this->faker->sentence
        ];

        $this->assertDatabaseMissing('tls_policies', $data);

        Session::start();

        $this->post(route('tls-policies.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(302);

        $this->actingAs($user)
            ->post(route('tls-policies.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('tls-policies.store'), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful();

        $this->assertDatabaseHas('tls_policies', $data);
    }

    public function testEdit()
    {
        $admin = factory(Mailbox::class)->create(['is_super_admin' => true, 'active' => true]);
        $user = factory(Mailbox::class)->create(['is_super_admin' => false, 'active' => true]);
        $tlsPolicy = factory(TlsPolicy::class)->create();

        $this->get(route('tls-policies.edit', compact('tlsPolicy')))
            ->assertStatus(302);

        $this->actingAs($user)
            ->get(route('tls-policies.edit', compact('tlsPolicy')))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->get(route('tls-policies.edit', compact('tlsPolicy')))
            ->assertSuccessful()
            ->assertSee(route('tls-policies.update', compact('tlsPolicy')));
    }

    public function testUpdate()
    {
        $admin = factory(Mailbox::class)->create(['is_super_admin' => true]);
        $user = factory(Mailbox::class)->create(['is_super_admin' => false]);
        $tlsPolicy = factory(TlsPolicy::class)->create();

        $data = [
            'domain'      => $tlsPolicy->domain,
            'policy'      => $this->faker->randomElement([
                'none',
                'may',
                'encrypt',
                'dane',
                'dane-only',
                'verify',
                'secure'
            ]),
            'params'      => 'match=.example.com',
            'description' => $this->faker->sentence
        ];

        $this->assertDatabaseMissing('tls_policies', $data);

        Session::start();

        $this->post(route('tls-policies.update', compact('tlsPolicy')), array_merge($data, [
            '_method' => 'PATCH',
            '_token'  => csrf_token()
        ]))
            ->assertStatus(302);

        $this->actingAs($user)
            ->post(route('tls-policies.update', compact('tlsPolicy')), array_merge($data, [
                '_method' => 'PATCH',
                '_token'  => csrf_token()
            ]))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('tls-policies.update', compact('tlsPolicy')), array_merge($data, [
                '_method' => 'PATCH',
                '_token'  => csrf_token()
            ]))
            ->assertSuccessful();

        $this->assertDatabaseHas('tls_policies', $data);
    }

    public function testDestroy()
    {
        $admin = factory(Mailbox::class)->create(['is_super_admin' => true, 'active' => true]);
        $user = factory(Mailbox::class)->create(['is_super_admin' => false, 'active' => true]);
        $tlsPolicy = factory(TlsPolicy::class)->create();

        Session::start();

        $this->post(route('tls-policies.destroy', compact('tlsPolicy')), ['_method' => 'PATCH', '_token' => csrf_token()])
            ->assertStatus(302);
        $this->assertNotNull($tlsPolicy->fresh());


        $this->actingAs($user)
            ->post(route('tls-policies.destroy', compact('tlsPolicy')), ['_method' => 'PATCH', '_token' => csrf_token()])
            ->assertStatus(403);
        $this->assertNotNull($tlsPolicy->fresh());

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('tls-policies.destroy', compact('tlsPolicy')), ['_method' => 'DELETE', '_token' => csrf_token()])
            ->assertSuccessful();
        $this->assertNull($tlsPolicy->fresh());
    }
}
