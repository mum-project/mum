<?php

namespace Tests\Unit;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\SizeMeasurement;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailboxTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testDomain()
    {
        $domain = Domain::factory()->create();
        $mailbox = Mailbox::factory()->create(['domain_id' => $domain->id]);
        $this->assertTrue($mailbox->fresh()->domain == $domain->fresh());
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testSizeMeasurements()
    {
        $mailbox = Mailbox::factory()->create();
        $size1 = SizeMeasurement::factory()->create(['measurable_id' => $mailbox->id, 'measurable_type' => Mailbox::class]);
        $size2 = SizeMeasurement::factory()->create(['measurable_id' => $mailbox->id, 'measurable_type' => Mailbox::class]);
        $this->assertTrue($mailbox->sizeMeasurements->contains($size1));
        $this->assertTrue($mailbox->sizeMeasurements->contains($size2));

        $otherMailbox = Mailbox::factory()->create();
        $otherSize = SizeMeasurement::factory()->create(['measurable_id' => $otherMailbox->id, 'measurable_type' => Mailbox::class]);
        $this->assertFalse($mailbox->sizeMeasurements->contains($otherSize));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testAdmins()
    {
        $mailbox = Mailbox::factory()->create();
        $admin1 = Mailbox::factory()->create();
        $admin2 = Mailbox::factory()->create();
        $mailbox->admins()->saveMany([
            $admin1,
            $admin2
        ]);
        $this->assertTrue($mailbox->fresh()->admins->contains($admin1));
        $this->assertTrue($mailbox->fresh()->admins->contains($admin2));

        $otherMailbox = Mailbox::factory()->create();
        $otherAdmin = Mailbox::factory()->create();
        $otherMailbox->admins()->save($otherAdmin);
        $this->assertFalse($mailbox->fresh()->admins->contains($otherAdmin));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testSendingAliases()
    {
        $mailbox = Mailbox::factory()->create();
        $alias1 = Alias::factory()->create();
        $alias2 = Alias::factory()->create();
        $mailbox->sendingAliases()->saveMany([
            $alias1,
            $alias2
        ]);
        $this->assertTrue($mailbox->fresh()->sendingAliases->contains($alias1));
        $this->assertTrue($mailbox->fresh()->sendingAliases->contains($alias2));

        $otherMailbox = Mailbox::factory()->create();
        $otherAlias = Alias::factory()->create();
        $otherMailbox->sendingAliases()->save($otherAlias);
        $this->assertFalse($mailbox->fresh()->sendingAliases->contains($otherAlias));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testReceivingAliases()
    {
        $mailbox = Mailbox::factory()->create();
        $alias1 = Alias::factory()->create();
        $alias2 = Alias::factory()->create();
        DB::table('alias_recipients')->insert([
            [
                'alias_id'          => $alias1->id,
                'recipient_address' => $mailbox->local_part . '@' . $mailbox->domain->domain,
                'mailbox_id'        => $mailbox->id
            ],
            [
                'alias_id'          => $alias2->id,
                'recipient_address' => $mailbox->local_part . '@' . $mailbox->domain->domain,
                'mailbox_id'        => $mailbox->id
            ]
        ]);
        $this->assertTrue($mailbox->receivingAliases->contains($alias1));
        $this->assertTrue($mailbox->receivingAliases->contains($alias2));

        $otherMailbox = Mailbox::factory()->create();
        $otherAlias = Alias::factory()->create();
        DB::table('alias_recipients')->insert([
            'alias_id'          => $otherAlias->id,
            'recipient_address' => $otherMailbox->local_part . '@' . $otherMailbox->domain->domain,
            'mailbox_id'        => $otherMailbox->id
        ]);
        $this->assertFalse($mailbox->receivingAliases->contains($otherAlias));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testAdministratedDomains()
    {
        $mailbox = Mailbox::factory()->create();
        $domain1 = Domain::factory()->create();
        $domain2 = Domain::factory()->create();
        $mailbox->administratedDomains()->saveMany([
            $domain1,
            $domain2
        ]);
        $this->assertTrue($mailbox->fresh()->administratedDomains->contains($domain1));
        $this->assertTrue($mailbox->fresh()->administratedDomains->contains($domain2));

        $otherMailbox = Mailbox::factory()->create();
        $otherDomain = Domain::factory()->create();
        $otherMailbox->administratedDomains()->save($otherDomain);
        $this->assertFalse($mailbox->fresh()->administratedDomains->contains($otherDomain));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testAdministratedMailboxes()
    {
        $admin = Mailbox::factory()->create();
        $mailbox1 = Mailbox::factory()->create();
        $mailbox2 = Mailbox::factory()->create();
        $admin->administratedMailboxes()->saveMany([
            $mailbox1,
            $mailbox2
        ]);
        $this->assertTrue($admin->fresh()->administratedMailboxes->contains($mailbox1));
        $this->assertTrue($admin->fresh()->administratedMailboxes->contains($mailbox2));

        $otherAdmin = Mailbox::factory()->create();
        $otherMailbox = Mailbox::factory()->create();
        $otherAdmin->administratedMailboxes()->save($otherMailbox);
        $this->assertFalse($admin->fresh()->administratedMailboxes->contains($otherMailbox));
    }

    public function testAddress()
    {
        $mailbox = Mailbox::factory()->create();
        $this->assertEquals($mailbox->local_part . '@' . $mailbox->domain->domain, $mailbox->address());
    }

    public function testRouteNotificationForAlternativeMail()
    {
        $mailbox = Mailbox::factory()->create(['alternative_email' => $this->faker->email]);
        $this->assertEquals($mailbox->routeNotificationFor('alternative_mail'), $mailbox->alternative_email);
    }

    public function testGetEmailForPasswordReset()
    {
        $mailbox = Mailbox::factory()->create(['alternative_email' => $this->faker->email]);
        $this->assertEquals($mailbox->getEmailForPasswordReset(), $mailbox->address());
    }

    public function testSendPasswordResetNotification()
    {
        Notification::fake();
        /** @var Mailbox $mailbox */
        $mailbox = Mailbox::factory()->create();
        $token = Str::random(10);
        $mailbox->sendPasswordResetNotification($token);
        Notification::assertSentTo($mailbox, ResetPassword::class, function ($notification, $channels) use ($token) {
            return $token === $notification->token;
        });
    }

    public function testSendPasswordResetNotificationWithAlternativeMail()
    {
        Notification::fake();
        /** @var Mailbox $mailbox */
        $mailbox = Mailbox::factory()->create(['alternative_email' => $this->faker->safeEmail]);
        $token = Str::random(10);
        $mailbox->sendPasswordResetNotification($token);
        Notification::assertSentTo($mailbox, ResetPassword::class, function ($notification, $channels) use ($token) {
            return $token === $notification->token;
        });
    }

    public function testIsSuperAdmin()
    {
        $admin = Mailbox::factory()->create(['is_super_admin' => true]);
        $mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
        $this->assertTrue($admin->isSuperAdmin());
        $this->assertFalse($mailbox->isSuperAdmin());
    }
}
