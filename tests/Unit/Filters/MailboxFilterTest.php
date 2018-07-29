<?php

namespace Tests\Unit\Filters;

use App\Alias;
use App\Domain;
use App\Http\Filters\MailboxFilter;
use App\Mailbox;
use function factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailboxFilterTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
    }

    /**
     * Creates a QueryFilter and injects a mocked Request object
     * that supplies the provided request parameters to the filter.
     *
     * @param array $requestParams
     * @return MailboxFilter
     */
    protected function createMailboxFilter(array $requestParams)
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($requestParams);
        return new MailboxFilter($mockRequest);
    }

    protected function assertForOneMailbox(MailboxFilter $mailboxFilter, Builder $builder, Mailbox $matchingMailbox)
    {
        $query = $mailboxFilter->apply($builder);
        $this->assertTrue($matchingMailbox->is($query->firstOrFail()));
        $this->assertTrue($query->get()
                ->count() === 1);
    }

    protected function assertAllBoolPossibilitiesFor($filterName, Mailbox $trueMailbox, Mailbox $falseMailbox)
    {
        $mailboxFilter = $this->createMailboxFilter([$filterName => '1']);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $trueMailbox);

        $mailboxFilter = $this->createMailboxFilter([$filterName => 'true']);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $trueMailbox);

        $mailboxFilter = $this->createMailboxFilter([$filterName => true]);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $trueMailbox);

        $mailboxFilter = $this->createMailboxFilter([$filterName => '0']);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $falseMailbox);

        $mailboxFilter = $this->createMailboxFilter([$filterName => 'false']);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $falseMailbox);

        $mailboxFilter = $this->createMailboxFilter([$filterName => false]);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $falseMailbox);
    }

    public function testDomain()
    {
        $filterDomain = factory(Domain::class)->create();
        $otherDomain = factory(Domain::class)->create();
        $matchingMailbox = factory(Mailbox::class)->create(['domain_id' => $filterDomain->id]);
        factory(Mailbox::class)->create(['domain_id' => $otherDomain->id]);
        $mailboxFilter = $this->createMailboxFilter(['domain' => $filterDomain->id]);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $matchingMailbox);
    }

    public function testDomainWithoutValue()
    {
        $mailboxFilter = $this->createMailboxFilter(['domain' => null]);
        $query = $mailboxFilter->apply(Mailbox::query());
        $this->assertTrue($query->doesntExist());
    }

    public function testDomainWithNonExistentValue()
    {
        $mailboxFilter = $this->createMailboxFilter(['domain' => 'non.existent.domain']);
        $query = $mailboxFilter->apply(Mailbox::query());
        $this->assertTrue($query->doesntExist());
    }

    public function testActive()
    {
        $activeMailbox = factory(Mailbox::class)->create(['active' => true]);
        $nonActiveMailbox = factory(Mailbox::class)->create(['active' => false]);

        $this->assertAllBoolPossibilitiesFor('active', $activeMailbox, $nonActiveMailbox);
    }

    public function testIsSuperAdmin()
    {
        $admin = factory(Mailbox::class)->create(['is_super_admin' => true]);
        $mailbox = factory(Mailbox::class)->create(['is_super_admin' => false]);

        $this->assertAllBoolPossibilitiesFor('isSuperAdmin', $admin, $mailbox);
    }

    public function testSendOnly()
    {
        $sendOnlyMailbox = factory(Mailbox::class)->create(['send_only' => true]);
        $mailbox = factory(Mailbox::class)->create(['send_only' => false]);

        $this->assertAllBoolPossibilitiesFor('sendOnly', $sendOnlyMailbox, $mailbox);
    }

    public function testHasName()
    {
        $mailboxWith = factory(Mailbox::class)->create(['name' => 'Foobar']);
        $mailboxWithout= factory(Mailbox::class)->create(['name' => null]);

        $this->assertAllBoolPossibilitiesFor('hasName', $mailboxWith, $mailboxWithout);
    }

    public function testHasAlternativeEmail()
    {
        $mailboxWith = factory(Mailbox::class)->create(['alternative_email' => 'foo@bar.com']);
        $mailboxWithout = factory(Mailbox::class)->create(['alternative_email' => null]);

        $this->assertAllBoolPossibilitiesFor('hasAlternativeEmail', $mailboxWith, $mailboxWithout);
    }

    public function testHasQuota()
    {
        $mailboxWith = factory(Mailbox::class)->create(['quota' => 42]);
        $mailboxWithout = factory(Mailbox::class)->create(['quota' => null]);

        $this->assertAllBoolPossibilitiesFor('hasQuota', $mailboxWith, $mailboxWithout);
    }

    public function testSendingAlias()
    {
        /** @var Mailbox $mailboxWith */
        $mailboxWith = factory(Mailbox::class)->create();
        $alias = factory(Alias::class)->create();
        $mailboxWith->sendingAliases()->save($alias);
        factory(Mailbox::class)->create();

        $mailboxFilter = $this->createMailboxFilter(['sendingAlias' => $alias->id]);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $mailboxWith);
    }

    public function testReceivingAlias()
    {
        $mailboxWith = factory(Mailbox::class)->create();
        /** @var Alias $alias */
        $alias = factory(Alias::class)->create();
        $alias->addRecipientMailbox($mailboxWith);
        factory(Mailbox::class)->create();

        $mailboxFilter = $this->createMailboxFilter(['receivingAlias' => $alias->id]);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $mailboxWith);
    }

    public function testAdministratedDomain()
    {
        /** @var Mailbox $mailboxWith */
        $mailboxWith = factory(Mailbox::class)->create();
        $domain = factory(Domain::class)->create();
        $mailboxWith->administratedDomains()->save($domain);
        factory(Mailbox::class)->create();

        $mailboxFilter = $this->createMailboxFilter(['administratedDomain' => $domain->id]);
        $this->assertForOneMailbox($mailboxFilter, Mailbox::query(), $mailboxWith);
    }

    public function testOrderByLocalPart()
    {
        $mailboxB = factory(Mailbox::class)->create(['local_part' => 'b']);
        $mailboxC = factory(Mailbox::class)->create(['local_part' => 'c']);
        $mailboxA = factory(Mailbox::class)->create(['local_part' => 'a']);

        $mailboxFilter = $this->createMailboxFilter(['orderByLocalPart' => 'asc']);
        $query = $mailboxFilter->apply(Mailbox::query());
        $results = $query->get();
        $this->assertTrue($mailboxA->is($results->get(0)));
        $this->assertTrue($mailboxB->is($results->get(1)));
        $this->assertTrue($mailboxC->is($results->get(2)));

        $mailboxFilter = $this->createMailboxFilter(['orderByLocalPart' => 'desc']);
        $query = $mailboxFilter->apply(Mailbox::query());
        $results = $query->get();
        $this->assertTrue($mailboxA->is($results->get(2)));
        $this->assertTrue($mailboxB->is($results->get(1)));
        $this->assertTrue($mailboxC->is($results->get(0)));
    }

    public function testOrderById()
    {
        $mailbox1 = factory(Mailbox::class)->create(['local_part' => 'b']);
        $mailbox2 = factory(Mailbox::class)->create(['local_part' => 'c']);
        $mailbox3 = factory(Mailbox::class)->create(['local_part' => 'a']);

        $mailboxFilter = $this->createMailboxFilter(['orderById' => 'asc']);
        $query = $mailboxFilter->apply(Mailbox::query());
        $results = $query->get();
        $this->assertTrue($mailbox1->is($results->get(0)));
        $this->assertTrue($mailbox2->is($results->get(1)));
        $this->assertTrue($mailbox3->is($results->get(2)));

        $mailboxFilter = $this->createMailboxFilter(['orderById' => 'desc']);
        $query = $mailboxFilter->apply(Mailbox::query());
        $results = $query->get();
        $this->assertTrue($mailbox1->is($results->get(2)));
        $this->assertTrue($mailbox2->is($results->get(1)));
        $this->assertTrue($mailbox3->is($results->get(0)));
    }

    public function testOrderByName()
    {
        $mailboxB = factory(Mailbox::class)->create(['name' => 'b']);
        $mailboxC = factory(Mailbox::class)->create(['name' => 'c']);
        $mailboxA = factory(Mailbox::class)->create(['name' => 'a']);

        $mailboxFilter = $this->createMailboxFilter(['orderByName' => 'asc']);
        $query = $mailboxFilter->apply(Mailbox::query());
        $results = $query->get();
        $this->assertTrue($mailboxA->is($results->get(0)));
        $this->assertTrue($mailboxB->is($results->get(1)));
        $this->assertTrue($mailboxC->is($results->get(2)));

        $mailboxFilter = $this->createMailboxFilter(['orderByName' => 'desc']);
        $query = $mailboxFilter->apply(Mailbox::query());
        $results = $query->get();
        $this->assertTrue($mailboxA->is($results->get(2)));
        $this->assertTrue($mailboxB->is($results->get(1)));
        $this->assertTrue($mailboxC->is($results->get(0)));
    }

    public function testSearch()
    {
        $otherMailbox = factory(Mailbox::class)->create();
        $foobarDomain = factory(Domain::class)->create(['domain' => 'foobar.com']);
        $mailboxLocalPart = factory(Mailbox::class)->create(['local_part' => 'foobarABC']);
        $mailboxName = factory(Mailbox::class)->create(['name' => 'foobarXYZ']);
        $mailboxDomain = factory(Mailbox::class)->create(['domain_id' => $foobarDomain]);

        $mailboxFilter = $this->createMailboxFilter(['search' => 'foobar']);
        $query = $mailboxFilter->apply(Mailbox::query());
        $results = $query->get();
        $this->assertTrue($results->contains($mailboxLocalPart));
        $this->assertTrue($results->contains($mailboxName));
        $this->assertTrue($results->contains($mailboxDomain));
        $this->assertFalse($results->contains($otherMailbox));
        $this->assertTrue($results->count() === 3);
    }

    public function testSearchWithoutKeyword()
    {
        $mailbox1 = factory(Mailbox::class)->create();
        $mailbox2 = factory(Mailbox::class)->create();

        $mailboxFilter = $this->createMailboxFilter(['search' => '']);
        $results = $mailboxFilter->apply(Mailbox::query())->get();
        $this->assertTrue($results->contains($mailbox1));
        $this->assertTrue($results->contains($mailbox2));
        $this->assertTrue($results->count() === 2);
    }
}
