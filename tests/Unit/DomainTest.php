<?php

namespace Tests\Unit;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\SizeMeasurement;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomainTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testMailboxes()
    {
        $domain = factory(Domain::class)->create();
        $mailbox1 = factory(Mailbox::class)->create(['domain_id' => $domain->id]);
        $mailbox2 = factory(Mailbox::class)->create(['domain_id' => $domain->id]);
        $this->assertTrue($domain->mailboxes->contains($mailbox1));
        $this->assertTrue($domain->mailboxes->contains($mailbox2));

        $otherDomain = factory(Domain::class)->create();
        $otherMailbox = factory(Mailbox::class)->create(['domain_id' => $otherDomain->id]);
        $this->assertFalse($domain->mailboxes->contains($otherMailbox));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testAliases()
    {
        $domain = factory(Domain::class)->create();
        $alias1 = factory(Alias::class)->create(['domain_id' => $domain->id]);
        $alias2 = factory(Alias::class)->create(['domain_id' => $domain->id]);
        $this->assertTrue($domain->aliases->contains($alias1));
        $this->assertTrue($domain->aliases->contains($alias2));

        $otherDomain = factory(Domain::class)->create();
        $otherAlias = factory(Alias::class)->create(['domain_id' => $otherDomain->id]);
        $this->assertFalse($domain->aliases->contains($otherAlias));
    }

    /**
     * Testing an Eloquent relationship
     */
    public function testSizeMeasurements()
    {
        $domain1 = factory(Domain::class)->create();
        $domain2 = factory(Domain::class)->create();
        factory(Mailbox::class)->create();
        $size1 = factory(SizeMeasurement::class)->create([
            'measurable_id'   => $domain1->id,
            'measurable_type' => Domain::class
        ]);
        $size2 = factory(SizeMeasurement::class)->create([
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
        $domain = factory(Domain::class)->create();
        $mailbox1 = factory(Mailbox::class)->create();
        $mailbox2 = factory(Mailbox::class)->create();
        $domain->admins()
            ->saveMany([
                $mailbox1,
                $mailbox2
            ]);
        $this->assertTrue($domain->fresh()->admins->contains($mailbox1));
        $this->assertTrue($domain->fresh()->admins->contains($mailbox2));

        $otherDomain = factory(Domain::class)->create();
        $otherMailbox = factory(Mailbox::class)->create();
        $otherDomain->admins()
            ->save($otherMailbox);
        $this->assertFalse($domain->fresh()->admins->contains($otherMailbox));
    }
}
