<?php

namespace Tests\Feature\Controllers;

use App\Domain;
use App\Http\Resources\SizeMeasurementResource;
use App\Mailbox;
use App\SizeMeasurement;
use function compact;
use function csrf_token;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SizeMeasurementControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @var Mailbox */
    protected $admin;

    /** @var Mailbox */
    protected $mailbox;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
        $this->admin = Mailbox::factory()->create(['is_super_admin' => true]);
        $this->mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
    }

    public function testIndexForDomain()
    {
        $domain = Domain::factory()->create();
        SizeMeasurement::factory( 30)->create([
            'measurable_id'   => $domain->id,
            'measurable_type' => Domain::class
        ]);

        $this->get(route('domains.sizes', compact('domain')))
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->followingRedirects()
            ->get(route('domains.sizes', compact('domain')))
            ->assertForbidden();

        $responseData = $this->actingAs($this->admin)
                            ->followingRedirects()
                            ->get(route('domains.sizes', compact('domain')))
                            ->assertSuccessful()
                            ->getOriginalContent()
                            ->getData()['sizeMeasurements'];

        SizeMeasurement::query()
            ->latest()
            ->get()
            ->each(function (SizeMeasurement $sizeMeasurement) use ($responseData) {
                $this->assertTrue($responseData->contains($sizeMeasurement));
            });
    }

    public function testIndexForMailbox()
    {
        $mailbox = Mailbox::factory()->create();
        SizeMeasurement::factory( 30)->create([
            'measurable_id'   => $mailbox->id,
            'measurable_type' => Mailbox::class
        ]);

        $this->get(route('mailboxes.sizes', compact('mailbox')))
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->followingRedirects()
            ->get(route('mailboxes.sizes', compact('mailbox')))
            ->assertForbidden();

        $responseData = $this->actingAs($this->admin)
                            ->followingRedirects()
                            ->get(route('mailboxes.sizes', compact('mailbox')))
                            ->assertSuccessful()
                            ->getOriginalContent()
                            ->getData()['sizeMeasurements'];

        SizeMeasurement::query()
            ->latest()
            ->get()
            ->each(function (SizeMeasurement $sizeMeasurement) use ($responseData) {
                $this->assertTrue($responseData->contains($sizeMeasurement));
            });
    }

    public function testDestroyForDomain()
    {
        /** @var Domain $domain */
        $domain = Domain::factory()->create();
        SizeMeasurement::factory( 30)->create([
            'measurable_id'   => $domain->id,
            'measurable_type' => Domain::class
        ]);

        Session::start();
        $this->delete(route('domains.sizes', compact('domain')), ['_token' => csrf_token()])
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->followingRedirects()
            ->delete(route('domains.sizes', compact('domain')), ['_token' => csrf_token()])
            ->assertForbidden();

        $this->assertTrue($domain->sizeMeasurements()
            ->exists());

        $this->actingAs($this->admin)
            ->followingRedirects()
            ->delete(route('domains.sizes', compact('domain')), ['_token' => csrf_token()])
            ->assertSuccessful();

        $this->assertTrue($domain->sizeMeasurements()
            ->doesntExist());
    }

    public function testDestroyForMailbox()
    {
        /** @var Domain $mailbox */
        $mailbox = Mailbox::factory()->create();
        SizeMeasurement::factory( 30)->create([
            'measurable_id'   => $mailbox->id,
            'measurable_type' => Mailbox::class
        ]);

        Session::start();
        $this->delete(route('mailboxes.sizes', compact('mailbox')), ['_token' => csrf_token()])
            ->assertRedirect(route('login'));

        $this->actingAs($this->mailbox)
            ->followingRedirects()
            ->delete(route('mailboxes.sizes', compact('mailbox')), ['_token' => csrf_token()])
            ->assertForbidden();

        $this->assertTrue($mailbox->sizeMeasurements()
            ->exists());

        $this->actingAs($this->admin)
            ->followingRedirects()
            ->delete(route('mailboxes.sizes', compact('mailbox')), ['_token' => csrf_token()])
            ->assertSuccessful();

        $this->assertTrue($mailbox->sizeMeasurements()
            ->doesntExist());
    }

    public function testIndexForDomainAsJson()
    {
        $domain = Domain::factory()->create();
        $sizes = SizeMeasurement::factory( 30)->create([
            'measurable_id'   => $domain->id,
            'measurable_type' => Domain::class
        ]);

        $this->getJson(route('domains.sizes', compact('domain')))
            ->assertStatus(401);

        $this->actingAs($this->mailbox)
            ->getJson(route('domains.sizes', compact('domain')))
            ->assertForbidden();

        $response = $this->actingAs($this->admin)
            ->getJson(route('domains.sizes', compact('domain')))
            ->assertSuccessful();

        $sizes->each(function (SizeMeasurement $sizeMeasurement) use ($response) {
            $response->assertJsonFragment((new SizeMeasurementResource($sizeMeasurement))->jsonSerialize());
        });
    }

    public function testIndexForMailboxAsJson()
    {
        $mailbox = Mailbox::factory()->create();
        $sizes = SizeMeasurement::factory( 30)->create([
            'measurable_id'   => $mailbox->id,
            'measurable_type' => Mailbox::class
        ]);

        $this->getJson(route('mailboxes.sizes', compact('mailbox')))
            ->assertStatus(401);

        $this->actingAs($this->mailbox)
            ->getJson(route('mailboxes.sizes', compact('mailbox')))
            ->assertForbidden();

        $response = $this->actingAs($this->admin)
            ->getJson(route('mailboxes.sizes', compact('mailbox')))
            ->assertSuccessful();

        $sizes->each(function (SizeMeasurement $sizeMeasurement) use ($response) {
            $response->assertJsonFragment((new SizeMeasurementResource($sizeMeasurement))->jsonSerialize());
        });
    }

    public function testDestroyForDomainAsJson()
    {
        /** @var Domain $domain */
        $domain = Domain::factory()->create();
        SizeMeasurement::factory( 30)->create([
            'measurable_id'   => $domain->id,
            'measurable_type' => Domain::class
        ]);

        Session::start();
        $this->deleteJson(route('domains.sizes', compact('domain')), ['_token' => csrf_token()])
            ->assertStatus(401);

        $this->actingAs($this->mailbox)
            ->deleteJson(route('domains.sizes', compact('domain')), ['_token' => csrf_token()])
            ->assertForbidden();

        $this->assertTrue($domain->sizeMeasurements()
            ->exists());

        $this->actingAs($this->admin)
            ->deleteJson(route('domains.sizes', compact('domain')), ['_token' => csrf_token()])
            ->assertSuccessful();

        $this->assertTrue($domain->sizeMeasurements()
            ->doesntExist());
    }

    public function testDestroyForMailboxAsJson()
    {
        /** @var Domain $mailbox */
        $mailbox = Mailbox::factory()->create();
        SizeMeasurement::factory( 30)->create([
            'measurable_id'   => $mailbox->id,
            'measurable_type' => Mailbox::class
        ]);

        Session::start();
        $this->deleteJson(route('mailboxes.sizes', compact('mailbox')), ['_token' => csrf_token()])
            ->assertStatus(401);

        $this->actingAs($this->mailbox)
            ->deleteJson(route('mailboxes.sizes', compact('mailbox')), ['_token' => csrf_token()])
            ->assertForbidden();

        $this->assertTrue($mailbox->sizeMeasurements()
            ->exists());

        $this->actingAs($this->admin)
            ->deleteJson(route('mailboxes.sizes', compact('mailbox')), ['_token' => csrf_token()])
            ->assertSuccessful();

        $this->assertTrue($mailbox->sizeMeasurements()
            ->doesntExist());
    }
}
