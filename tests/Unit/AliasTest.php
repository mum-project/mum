<?php

namespace Tests\Unit;

use App\Alias;
use App\Domain;
use App\Mailbox;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AliasTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
        Mailbox::factory()->create();
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testDomain()
    {
        $domain = Domain::factory()->create();
        $alias = Alias::factory()->create(['domain_id' => $domain->id]);
        $this->assertTrue($alias->domain == $domain->fresh());
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testSenderMailboxes()
    {
        $alias = Alias::factory()->create();
        $sender1 = Mailbox::factory()->create();
        $sender2 = Mailbox::factory()->create();
        $alias->senderMailboxes()
            ->saveMany([
                $sender1,
                $sender2
            ]);
        $this->assertTrue($alias->senderMailboxes->contains($sender1));
        $this->assertTrue($alias->senderMailboxes->contains($sender2));

        $otherAlias = Alias::factory()->create();
        $otherSender = Mailbox::factory()->create();
        $otherAlias->senderMailboxes()
            ->save($otherSender);
        $this->assertFalse($alias->senderMailboxes->contains($otherSender));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testRecipientMailboxes()
    {
        $alias = Alias::factory()->create();
        $recipient1 = Mailbox::factory()->create();
        $recipient2 = Mailbox::factory()->create();
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

        $otherAlias = Alias::factory()->create();
        $otherRecipient = Mailbox::factory()->create();
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
        $alias = Alias::factory()->create();
        $recipient1 = Mailbox::factory()->create();
        $recipient2 = Mailbox::factory()->create();
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
        $otherAlias = Alias::factory()->create();
        $otherRecipient = Mailbox::factory()->create();
        $otherAlias->addRecipientMailbox($otherRecipient);
        $this->assertFalse($alias->recipientAddresses()->contains($otherRecipient->local_part . '@' .
            $otherRecipient->domain->domain));
    }
}
