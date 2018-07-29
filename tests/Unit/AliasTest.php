<?php

namespace Tests\Unit;

use App\Alias;
use App\Domain;
use App\Mailbox;
use function factory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AliasTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
        factory(Mailbox::class)->create();
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testDomain()
    {
        $domain = factory(Domain::class)->create();
        $alias = factory(Alias::class)->create(['domain_id' => $domain->id]);
        $this->assertTrue($alias->domain == $domain->fresh());
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testSenderMailboxes()
    {
        $alias = factory(Alias::class)->create();
        $sender1 = factory(Mailbox::class)->create();
        $sender2 = factory(Mailbox::class)->create();
        $alias->senderMailboxes()
            ->saveMany([
                $sender1,
                $sender2
            ]);
        $this->assertTrue($alias->senderMailboxes->contains($sender1));
        $this->assertTrue($alias->senderMailboxes->contains($sender2));

        $otherAlias = factory(Alias::class)->create();
        $otherSender = factory(Mailbox::class)->create();
        $otherAlias->senderMailboxes()
            ->save($otherSender);
        $this->assertFalse($alias->senderMailboxes->contains($otherSender));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testRecipientMailboxes()
    {
        $alias = factory(Alias::class)->create();
        $recipient1 = factory(Mailbox::class)->create();
        $recipient2 = factory(Mailbox::class)->create();
        DB::table('alias_recipients')
            ->insert([
                [
                    'alias_id'          => $alias->id,
                    'recipient_address' => $recipient1->local_part . '@' . $recipient1->domain->domain,
                    'mailbox_id'        => $recipient1->id
                ],
                [
                    'alias_id'          => $alias->id,
                    'recipient_address' => $recipient2->local_part . '@' . $recipient2->domain->domain,
                    'mailbox_id'        => $recipient2->id
                ]
            ]);
        $this->assertTrue($alias->recipientMailboxes->contains($recipient1));
        $this->assertTrue($alias->recipientMailboxes->contains($recipient2));

        $otherAlias = factory(Alias::class)->create();
        $otherRecipient = factory(Mailbox::class)->create();
        DB::table('alias_recipients')
            ->insert([
                'alias_id'          => $otherAlias->id,
                'recipient_address' => $otherRecipient->local_part . '@' . $otherRecipient->domain->domain,
                'mailbox_id'        => $otherRecipient->id
            ]);
        $this->assertFalse($alias->recipientMailboxes->contains($otherRecipient));
    }

    public function testRecipientAddresses()
    {
        /** @var Alias $alias */
        $alias = factory(Alias::class)->create();
        $recipient1 = factory(Mailbox::class)->create();
        $recipient2 = factory(Mailbox::class)->create();
        $externalEmail = $this->faker->safeEmail;
        $alias->addRecipientMailbox($recipient1);
        $alias->addRecipientMailbox($recipient2);
        $alias->addExternalRecipient($externalEmail);

        $this->assertTrue($alias->recipientAddresses()->contains($recipient1->local_part . '@' .
            $recipient1->domain->domain));
        $this->assertTrue($alias->recipientAddresses()->contains($recipient2->local_part . '@' .
            $recipient2->domain->domain));
        $this->assertTrue($alias->recipientAddresses()->contains($externalEmail));

        /** @var Alias $otherAlias */
        $otherAlias = factory(Alias::class)->create();
        $otherRecipient = factory(Mailbox::class)->create();
        $otherAlias->addRecipientMailbox($otherRecipient);
        $this->assertFalse($alias->recipientAddresses()->contains($otherRecipient->local_part . '@' .
            $otherRecipient->domain->domain));
    }
}
