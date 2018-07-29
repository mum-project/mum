<?php

namespace Tests\Unit\Filters;

use App\Domain;
use App\Http\Filters\DomainFilter;
use App\Mailbox;
use function factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomainFilterTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * Creates a QueryFilter and injects a mocked Request object
     * that supplies the provided request parameters to the filter.
     *
     * @param array $requestParams
     * @return DomainFilter
     */
    protected function createDomainFilter(array $requestParams)
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($requestParams);
        return new DomainFilter($mockRequest);
    }

    protected function assertForOneDomain(DomainFilter $domainFilter, Builder $builder, Domain $matchingDomain)
    {
        $query = $domainFilter->apply($builder);
        $this->assertTrue($matchingDomain->is($query->firstOrFail()));
        $this->assertTrue($query->get()
                ->count() === 1);
    }

    protected function assertAllBoolPossibilitiesFor($filterName, Domain $trueDomain, Domain $falseDomain)
    {
        $mailboxFilter = $this->createDomainFilter([$filterName => '1']);
        $this->assertForOneDomain($mailboxFilter, Domain::query(), $trueDomain);

        $mailboxFilter = $this->createDomainFilter([$filterName => 'true']);
        $this->assertForOneDomain($mailboxFilter, Domain::query(), $trueDomain);

        $mailboxFilter = $this->createDomainFilter([$filterName => true]);
        $this->assertForOneDomain($mailboxFilter, Domain::query(), $trueDomain);

        $mailboxFilter = $this->createDomainFilter([$filterName => '0']);
        $this->assertForOneDomain($mailboxFilter, Domain::query(), $falseDomain);

        $mailboxFilter = $this->createDomainFilter([$filterName => 'false']);
        $this->assertForOneDomain($mailboxFilter, Domain::query(), $falseDomain);

        $mailboxFilter = $this->createDomainFilter([$filterName => false]);
        $this->assertForOneDomain($mailboxFilter, Domain::query(), $falseDomain);
    }

    public function testActive()
    {
        $domainWith = factory(Domain::class)->create(['active' => true]);
        $domainWithout = factory(Domain::class)->create(['active' => false]);

        $this->assertAllBoolPossibilitiesFor('active', $domainWith, $domainWithout);
    }

    public function testAdmin()
    {
        $domain = factory(Domain::class)->create();
        /** @var Mailbox $admin */
        $admin = factory(Mailbox::class)->create();
        $admin->administratedDomains()->attach($domain);
        $otherDomain = factory(Domain::class)->create();
        $otherAdmin = factory(Mailbox::class)->create();
        $otherAdmin->administratedDomains()->attach($otherDomain);

        $domainFilter = $this->createDomainFilter(['admin' => $admin->id]);
        $results = $domainFilter->apply(Domain::query())->get();
        $this->assertTrue($results->contains($domain));
        $this->assertFalse($results->contains($otherDomain));
        $this->assertTrue($results->count() === 1);

        $domainFilter = $this->createDomainFilter(['admin' => $otherAdmin->id]);
        $results = $domainFilter->apply(Domain::query())->get();
        $this->assertFalse($results->contains($domain));
        $this->assertTrue($results->contains($otherDomain));
        $this->assertTrue($results->count() === 1);
    }

    public function testOrderById()
    {
        $domain1 = factory(Domain::class)->create(['domain' => 'b.local']);
        $domain2 = factory(Domain::class)->create(['domain' => 'c.local']);
        $domain3 = factory(Domain::class)->create(['domain' => 'a.local']);

        $domainFilter = $this->createDomainFilter(['orderById' => 'asc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(0)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(2)));

        $domainFilter = $this->createDomainFilter(['orderById' => 'desc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(2)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(0)));
    }

    public function testOrderByDomain()
    {
        $domainB = factory(Domain::class)->create(['domain' => 'b.local']);
        $domainC = factory(Domain::class)->create(['domain' => 'c.local']);
        $domainA = factory(Domain::class)->create(['domain' => 'a.local']);

        $domainFilter = $this->createDomainFilter(['orderByDomain' => 'asc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domainA->is($results->get(0)));
        $this->assertTrue($domainB->is($results->get(1)));
        $this->assertTrue($domainC->is($results->get(2)));

        $domainFilter = $this->createDomainFilter(['orderByDomain' => 'desc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domainA->is($results->get(2)));
        $this->assertTrue($domainB->is($results->get(1)));
        $this->assertTrue($domainC->is($results->get(0)));
    }

    public function testOrderByQuota()
    {
        $domain3 = factory(Domain::class)->create(['quota' => 3]);
        $domain1 = factory(Domain::class)->create(['quota' => 1]);
        $domain2 = factory(Domain::class)->create(['quota' => 2]);

        $domainFilter = $this->createDomainFilter(['orderByQuota' => 'asc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(0)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(2)));

        $domainFilter = $this->createDomainFilter(['orderByQuota' => 'desc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(2)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(0)));
    }

    public function testOrderByMaxQuota()
    {
        $domain3 = factory(Domain::class)->create(['max_quota' => 3]);
        $domain1 = factory(Domain::class)->create(['max_quota' => 1]);
        $domain2 = factory(Domain::class)->create(['max_quota' => 2]);

        $domainFilter = $this->createDomainFilter(['orderByMaxQuota' => 'asc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(0)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(2)));

        $domainFilter = $this->createDomainFilter(['orderByMaxQuota' => 'desc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(2)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(0)));
    }

    public function testOrderByMaxAliases()
    {
        $domain3 = factory(Domain::class)->create(['max_aliases' => 3]);
        $domain1 = factory(Domain::class)->create(['max_aliases' => 1]);
        $domain2 = factory(Domain::class)->create(['max_aliases' => 2]);

        $domainFilter = $this->createDomainFilter(['orderByMaxAliases' => 'asc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(0)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(2)));

        $domainFilter = $this->createDomainFilter(['orderByMaxAliases' => 'desc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(2)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(0)));
    }

    public function testOrderByMaxMailboxes()
    {
        $domain3 = factory(Domain::class)->create(['max_mailboxes' => 3]);
        $domain1 = factory(Domain::class)->create(['max_mailboxes' => 1]);
        $domain2 = factory(Domain::class)->create(['max_mailboxes' => 2]);

        $domainFilter = $this->createDomainFilter(['orderByMaxMailboxes' => 'asc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(0)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(2)));

        $domainFilter = $this->createDomainFilter(['orderByMaxMailboxes' => 'desc']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(2)));
        $this->assertTrue($domain2->is($results->get(1)));
        $this->assertTrue($domain3->is($results->get(0)));
    }

    public function testSearch()
    {
        $domain1 = factory(Domain::class)->create(['domain' => 'foobarABC']);
        $domain2 = factory(Domain::class)->create(['domain' => 'some.other.domain']);

        $domainFilter = $this->createDomainFilter(['search' => 'foobar']);
        $query = $domainFilter->apply(Domain::query());
        $results = $query->get();
        $this->assertTrue($domain1->is($results->get(0)));
        $this->assertFalse($domain2->is($results->get(1)));
    }
}
