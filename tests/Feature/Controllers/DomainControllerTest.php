<?php

namespace Tests\Feature\Controllers;

use App\Domain;
use App\Mailbox;
use function array_merge;
use function compact;
use function csrf_token;
use function factory;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomainControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
        $this->admin = Mailbox::factory()->create([
            'active'         => true,
            'is_super_admin' => true
        ]);
    }

    /**
     * Test pagination
     */
    public function testIndex()
    {
        $perPage = (new Domain())->getPerPage();
        Domain::factory( $perPage * 2 - 1)->create();
        $domainsPage1 = Domain::whereAuthorized()
            ->take($perPage)
            ->pluck('domain')
            ->toArray();
        $domainsPage2 = Domain::whereAuthorized()
            ->offset($perPage)
            ->take($perPage)
            ->pluck('domain')
            ->toArray();
        $response = $this->actingAs($this->admin)
            ->get(route('domains.index'));
        $response->assertSuccessful();
        $response->assertSeeTextInOrder($domainsPage1);
        $response2 = $this->actingAs($this->admin)
            ->get(route('domains.index', ['page' => '2']));
        $response2->assertSuccessful();
        $response2->assertSeeTextInOrder($domainsPage2);
    }

    public function testCreate()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('domains.create'));
        $response->assertSuccessful();
    }

    public function testStore()
    {
        Session::start();
        $data = [
            'domain'        => $this->faker->unique()->domainName,
            'description'   => $this->faker->sentence,
            'quota'         => 0,
            'max_quota'     => 0,
            'max_aliases'   => $this->faker->boolean ? null : 200,
            'max_mailboxes' => $this->faker->boolean ? null : 200
        ];
        $response = $this->actingAs($this->admin)
            ->followingRedirects()
            ->post(route('domains.store'), array_merge($data, ['_token' => csrf_token()]));
        $response->assertSuccessful();
        $response->assertSeeText($data['domain']);
        $this->assertDatabaseHas('domains', $data);
    }

    public function testShow()
    {
        $domain = Domain::factory()->create();
        $response = $this->actingAs($this->admin)
            ->get(route('domains.show', compact('domain')));
        $response->assertSuccessful();
        $response->assertSeeText($domain->domain);
    }

    public function testEdit()
    {
        $domain = Domain::factory()->create();
        $response = $this->actingAs($this->admin)
            ->get(route('domains.edit', compact('domain')));
        $response->assertSuccessful();
        $response->assertSeeText($domain->domain);
    }

    public function testUpdate()
    {
        Session::start();
        $domain = Domain::factory()->create();
        $data = [
            'description'   => 'foobar',
            'max_mailboxes' => '250'
        ];
        $this->actingAs($this->admin)
            ->followingRedirects()
            ->patch(route('domains.update', compact('domain')), array_merge($data, ['_token' => csrf_token()]))
            ->assertSuccessful()
            ->assertSeeText($domain->domain);

        $this->assertDatabaseHas('domains', array_merge(['id' => $domain->id], $data));
    }

    public function testDestroy()
    {
        Session::start();
        $domain = Domain::factory()->create();
        $this->assertDatabaseHas('domains', $domain->toArray());
        $response = $this->followingRedirects()
            ->actingAs($this->admin)
            ->delete(route('domains.update', compact('domain')), ['_token' => csrf_token()]);
        $response->assertSuccessful();
        $response->assertSeeText($domain->domain);
        $this->assertDatabaseMissing('domains', $domain->toArray());
    }
}
