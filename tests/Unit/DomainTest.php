<?php

namespace Tests\Unit;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\SizeMeasurement;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomainTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testMailboxes()
    {
        $domain = Domain::factory()->create();
        $mailbox1 = Mailbox::factory()->create(['domain_id' => $domain->id]);
        $mailbox2 = Mailbox::factory()->create(['domain_id' => $domain->id]);
        $this->assertTrue($domain->mailboxes->contains($mailbox1));
        $this->assertTrue($domain->mailboxes->contains($mailbox2));

        $otherDomain = Domain::factory()->create();
        $otherMailbox = Mailbox::factory()->create(['domain_id' => $otherDomain->id]);
        $this->assertFalse($domain->mailboxes->contains($otherMailbox));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testAliases()
    {
        $domain = Domain::factory()->create();
        $alias1 = Alias::factory()->create(['domain_id' => $domain->id]);
        $alias2 = Alias::factory()->create(['domain_id' => $domain->id]);
        $this->assertTrue($domain->aliases->contains($alias1));
        $this->assertTrue($domain->aliases->contains($alias2));

        $otherDomain = Domain::factory()->create();
        $otherAlias = Alias::factory()->create(['domain_id' => $otherDomain->id]);
        $this->assertFalse($domain->aliases->contains($otherAlias));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testSizeMeasurements()
    {
        $domain1 = Domain::factory()->create();
        $domain2 = Domain::factory()->create();
        Mailbox::factory()->create();
        $size1 = SizeMeasurement::factory()->create([
            'measurable_id'   => $domain1->id,
            'measurable_type' => Domain::class
        ]);
        $size2 = SizeMeasurement::factory()->create([
            'measurable_id'   => $domain2->id,
            'measurable_type' => Domain::class
        ]);
        $this->assertTrue($domain1->sizeMeasurements->contains($size1));
        $this->assertTrue($domain2->sizeMeasurements->contains($size2));
        $this->assertFalse($domain1->sizeMeasurements->contains($size2));
        $this->assertFalse($domain2->sizeMeasurements->contains($size1));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testAdmins()
    {
        $domain = Domain::factory()->create();
        $mailbox1 = Mailbox::factory()->create();
        $mailbox2 = Mailbox::factory()->create();
        $domain->admins()
            ->saveMany([
                $mailbox1,
                $mailbox2
            ]);
        $this->assertTrue($domain->fresh()->admins->contains($mailbox1));
        $this->assertTrue($domain->fresh()->admins->contains($mailbox2));

        $otherDomain = Domain::factory()->create();
        $otherMailbox = Mailbox::factory()->create();
        $otherDomain->admins()
            ->save($otherMailbox);
        $this->assertFalse($domain->fresh()->admins->contains($otherMailbox));
    }
}
