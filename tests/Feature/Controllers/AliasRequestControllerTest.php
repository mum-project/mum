<?php

namespace Tests\Feature\Controllers;

use App\AliasRequest;
use App\Domain;
use App\Http\Resources\AliasRequestResource;
use App\Mailbox;
use App\Notifications\AliasRequestCreatedNotification;
use App\Notifications\AliasRequestStatusNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AliasRequestControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    private $admin;

    private $mailbox;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
        Mailbox::factory()->create();
        $this->admin = Mailbox::factory()->create([
            'active'         => true,
            'is_super_admin' => true
        ]);
        $this->mailbox = Mailbox::factory()->create([
            'active'         => true,
            'is_super_admin' => false
        ]);
        Notification::fake();
    }

    public function testIndex()
    {
        $perPage = (new AliasRequest)->getPerPage();
        $localParts1 = AliasRequest::factory( $perPage)
            ->create(['status' => 'open'])
            ->pluck('local_part')
            ->toArray();
        $localParts2 = AliasRequest::factory( $perPage)
            ->create(['status' => 'open'])
            ->pluck('local_part')
            ->toArray();

        $response = $this->actingAs($this->admin)
            ->get(route('alias-requests.index', ['status' => 'open']));
        $response->assertSuccessful();

        foreach ($localParts1 as $localPart) {
            $response->assertSeeText($localPart);
        }
        foreach ($localParts2 as $localPart) {
            $response->assertDontSeeText($localPart);
        }

        $response2 = $this->actingAs($this->admin)
            ->get(route('alias-requests.index', [
                'page'   => '2',
                'status' => 'open'
            ]));
        $response2->assertSuccessful();
        $response2->assertSeeTextInOrder($localParts2);
        foreach ($localParts1 as $localPart) {
            $response2->assertDontSeeText($localPart);
        }
        foreach ($localParts2 as $localPart) {
            $response2->assertSeeText($localPart);
        }
    }

    public function testIndexJson()
    {
        $perPage = (new AliasRequest)->getPerPage();
        $aliasRequests1 = AliasRequest::factory( $perPage)->create(['status' => 'open']);

        $aliasRequests2 = AliasRequest::factory( $perPage)->create(['status' => 'open']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('alias-requests.index', ['status' => 'open']));
        $response->assertSuccessful();

        $response2 = $this->actingAs($this->admin)
            ->getJson(route('alias-requests.index', [
                'page'   => '2',
                'status' => 'open'
            ]));
        $response2->assertSuccessful();


        $aliasRequests1->each(function (AliasRequest $aliasRequest) use ($response, $response2) {
            $response->assertJsonFragment((new AliasRequestResource($aliasRequest))->jsonSerialize());
            $response2->assertJsonMissingExact((new AliasRequestResource($aliasRequest))->jsonSerialize());
        });

        $aliasRequests2->each(function (AliasRequest $aliasRequest) use ($response, $response2) {
            $response2->assertJsonFragment((new AliasRequestResource($aliasRequest))->jsonSerialize());
            $response->assertJsonMissingExact((new AliasRequestResource($aliasRequest))->jsonSerialize());
        });
    }

    public function testCreate()
    {
        $this->get(route('alias-requests.create'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->admin)
            ->get(route('alias-requests.create'))
            ->assertForbidden();

        $this->actingAs($this->mailbox)
            ->get(route('alias-requests.create'))
            ->assertSuccessful();
    }

    public function testStore()
    {
        Session::start();
        $domain = Domain::factory()->create();
        $mailbox = Mailbox::factory()->create(['domain_id' => $domain->id]);
        $data = [
            'domain_id'           => $domain->id,
            'local_part'          => $this->faker->userName,
            'sender_mailboxes'    => [
                [
                    'id'      => $mailbox->id,
                    'address' => $mailbox->address()
                ]
            ],
            'recipient_mailboxes' => [
                [
                    'id'      => $mailbox->id,
                    'address' => $mailbox->address()
                ]
            ],
        ];

        $this->get(route('alias-requests.create'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->admin)
            ->post(route('alias-requests.store', array_merge($data, ['_token' => csrf_token()])))
            ->assertForbidden();

        $this->assertDatabaseMissing('alias_requests', Arr::except($data, [
            'sender_mailboxes',
            'recipient_mailboxes'
        ]));

        $this->followingRedirects()
            ->actingAs($this->mailbox)
            ->post(route('alias-requests.store', array_merge($data, ['_token' => csrf_token()])))
            ->assertSuccessful();

        $this->assertDatabaseHas('alias_requests', Arr::except($data, [
            'sender_mailboxes',
            'recipient_mailboxes'
        ]));

        /** @var AliasRequest $aliasRequerst */
        $aliasRequerst = AliasRequest::query()
            ->where('local_part', $data['local_part'])
            ->where('domain_id', $data['domain_id'])
            ->firstOrFail();
        $this->assertTrue($aliasRequerst->senderMailboxes()
            ->where('mailbox_id', $mailbox->id)
            ->exists());
        $this->assertTrue($aliasRequerst->recipientMailboxes()
            ->where('mailbox_id', $mailbox->id)
            ->exists());

        Notification::assertSentTo(Mailbox::query()
            ->isSuperAdmin()
            ->get(), AliasRequestCreatedNotification::class);
    }

    public function testShow()
    {
        $aliasRequest = AliasRequest::factory()->create();
        $response = $this->actingAs($this->admin)
            ->get(route('alias-requests.show', compact('aliasRequest')));
        $response->assertSuccessful();
        $response->assertSeeText($aliasRequest->local_part);
    }

    public function testEdit()
    {
        $aliasRequest = AliasRequest::factory()->create();
        $response = $this->actingAs($this->admin)
            ->get(route('alias-requests.edit', compact('aliasRequest')));
        $response->assertSuccessful();
        $response->assertSeeText($aliasRequest->local_part);
    }

    public function testUpdate()
    {
        Session::start();

        /** @var AliasRequest $aliasRequest */
        $aliasRequest = AliasRequest::factory()->create();
        $oldMailbox = Mailbox::factory()->create();
        $newMailbox1 = Mailbox::factory()->create();
        $newMailbox2 = Mailbox::factory()->create();
        $aliasRequest->senderMailboxes()
            ->attach($oldMailbox);
        $aliasRequest->addRecipientMailbox($oldMailbox);

        $data = [
            'description'         => 'my new description',
            'sender_mailboxes'    => [
                [
                    'id'      => $newMailbox1->id,
                    'address' => $newMailbox1->address()
                ]
            ],
            'recipient_mailboxes' => [
                [
                    'id'      => $newMailbox2->id,
                    'address' => $newMailbox2->address()
                ]
            ],
            'external_recipients' => [
                ['address' => $this->faker->email],
                ['address' => $this->faker->email],
            ]
        ];

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->patch(
                route('alias-requests.update', compact('aliasRequest')),
                array_merge($data, ['_token' => csrf_token()])
            )
            ->assertSuccessful();

        $aliasRequest = $aliasRequest->fresh();

        $this->assertEquals($aliasRequest->description, $data['description']);
        $this->assertTrue($aliasRequest->senderMailboxes()
            ->where('mailbox_id', $newMailbox1->id)
            ->exists());
        $this->assertTrue($aliasRequest->recipientMailboxes()
            ->where('mailbox_id', $newMailbox2->id)
            ->exists());
        $this->assertNotNull($aliasRequest->externalRecipients()
            ->where('recipient_address', $data['external_recipients'][0]['address']));
        $this->assertNotNull($aliasRequest->externalRecipients()
            ->where('recipient_address', $data['external_recipients'][1]['address']));
    }

    public function testUpdateStatusToApproved()
    {
        Session::start();

        /** @var AliasRequest $aliasRequest */
        $aliasRequest = AliasRequest::factory()->create(['status' => 'open']);

        $data = [
            'status' => 'approved',
        ];

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->patch(
                route('alias-requests.status', compact('aliasRequest')),
                array_merge($data, ['_token' => csrf_token()])
            )
            ->assertSuccessful();

        $aliasRequest = $aliasRequest->fresh();

        $this->assertEquals($aliasRequest->status, $data['status']);

        Notification::assertSentTo($aliasRequest->mailbox, AliasRequestStatusNotification::class);

        $this->assertDatabaseHas('aliases', [
            'local_part' => $aliasRequest->local_part,
            'domain_id'  => $aliasRequest->domain_id
        ]);
    }

    public function testUpdateStatusToDismissed()
    {
        Session::start();

        /** @var AliasRequest $aliasRequest */
        $aliasRequest = AliasRequest::factory()->create(['status' => 'open']);

        $data = [
            'status' => 'dismissed',
        ];

        $this->followingRedirects()
            ->actingAs($this->admin)
            ->patch(
                route('alias-requests.status', compact('aliasRequest')),
                array_merge($data, ['_token' => csrf_token()])
            )
            ->assertSuccessful();

        $aliasRequest = $aliasRequest->fresh();

        $this->assertEquals($aliasRequest->status, $data['status']);

        Notification::assertSentTo($aliasRequest->mailbox, AliasRequestStatusNotification::class);

        $this->assertDatabaseMissing('aliases', [
            'local_part' => $aliasRequest->local_part,
            'domain_id'  => $aliasRequest->domain_id
        ]);
    }

    public function testDestroy()
    {
        Session::start();

        /** @var AliasRequest $aliasRequest */
        $aliasRequest = AliasRequest::factory()->create();

        $response = $this->followingRedirects()
            ->actingAs($this->admin)
            ->delete(route('alias-requests.destroy', compact('aliasRequest')), ['_token' => csrf_token()]);
        $response->assertSuccessful();
        $this->assertDatabaseMissing('alias_requests', $aliasRequest->toArray());
    }
}
