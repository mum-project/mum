<?php

namespace Tests\Unit\Filters;

use App\AliasRequest;
use App\Domain;
use App\Http\Filters\AliasRequestFilter;
use App\Mailbox;
use function factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AliasRequestFilterTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
        factory(Mailbox::class)->create();
    }

    /**
     * Creates a QueryFilter and injects a mocked Request object
     * that supplies the provided request parameters to the filter.
     *
     * @param array $requestParams
     * @return AliasRequestFilter
     */
    protected function createAliasRequestFilter(array $requestParams)
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($requestParams);
        return new AliasRequestFilter($mockRequest);
    }

    protected function assertForOneAliasRequest(
        AliasRequestFilter $aliasRequestFilter,
        Builder $builder,
                                                AliasRequest $aliasRequest
    ) {
        $query = $aliasRequestFilter->apply($builder);
        $this->assertTrue($aliasRequest->is($query->firstOrFail()));
        $this->assertTrue($query->get()
                ->count() === 1);
    }

    public function testDomain()
    {
        $domain = factory(Domain::class)->create();
        $aliasRequest = factory(AliasRequest::class)->create(['domain_id' => $domain->id]);
        $otherDomain = factory(Domain::class)->create();
        $otherAliasRequest = factory(AliasRequest::class)->create(['domain_id' => $otherDomain->id]);

        $aliasRequestFilter = $this->createAliasRequestFilter(['domain' => $domain->id]);
        $this->assertForOneAliasRequest($aliasRequestFilter, AliasRequest::query(), $aliasRequest);

        $aliasRequestFilter = $this->createAliasRequestFilter(['domain' => $otherDomain->id]);
        $this->assertForOneAliasRequest($aliasRequestFilter, AliasRequest::query(), $otherAliasRequest);
    }

    public function testOrderById()
    {
        $aliasRequest1 = factory(AliasRequest::class)->create(['local_part' => 'b']);
        $aliasRequest2 = factory(AliasRequest::class)->create(['local_part' => 'c']);
        $aliasRequest3 = factory(AliasRequest::class)->create(['local_part' => 'a']);

        $aliasRequestFilter = $this->createAliasRequestFilter(['orderById' => 'asc']);
        $query = $aliasRequestFilter->apply(AliasRequest::query());
        $results = $query->get();
        $this->assertTrue($aliasRequest1->is($results->get(0)));
        $this->assertTrue($aliasRequest2->is($results->get(1)));
        $this->assertTrue($aliasRequest3->is($results->get(2)));

        $aliasRequestFilter = $this->createAliasRequestFilter(['orderById' => 'desc']);
        $query = $aliasRequestFilter->apply(AliasRequest::query());
        $results = $query->get();
        $this->assertTrue($aliasRequest1->is($results->get(2)));
        $this->assertTrue($aliasRequest2->is($results->get(1)));
        $this->assertTrue($aliasRequest3->is($results->get(0)));
    }

    public function testOrderByLocalPart()
    {
        $aliasRequestB = factory(AliasRequest::class)->create(['local_part' => 'b']);
        $aliasRequestC = factory(AliasRequest::class)->create(['local_part' => 'c']);
        $aliasRequestA = factory(AliasRequest::class)->create(['local_part' => 'a']);

        $aliasRequestFilter = $this->createAliasRequestFilter(['orderByLocalPart' => 'asc']);
        $query = $aliasRequestFilter->apply(AliasRequest::query());
        $results = $query->get();
        $this->assertTrue($aliasRequestA->is($results->get(0)));
        $this->assertTrue($aliasRequestB->is($results->get(1)));
        $this->assertTrue($aliasRequestC->is($results->get(2)));

        $aliasRequestFilter = $this->createAliasRequestFilter(['orderByLocalPart' => 'desc']);
        $query = $aliasRequestFilter->apply(AliasRequest::query());
        $results = $query->get();
        $this->assertTrue($aliasRequestA->is($results->get(2)));
        $this->assertTrue($aliasRequestB->is($results->get(1)));
        $this->assertTrue($aliasRequestC->is($results->get(0)));
    }

    public function testSearch()
    {
        $otherAliasRequest = factory(AliasRequest::class)->create();
        $foobarDomain = factory(Domain::class)->create(['domain' => 'foobar.com']);
        $aliasRequestLocalPart = factory(AliasRequest::class)->create(['local_part' => 'foobarABC']);
        $aliasRequestDomain = factory(AliasRequest::class)->create(['domain_id' => $foobarDomain]);

        $aliasRequestFilter = $this->createAliasRequestFilter(['search' => 'foobar']);
        $query = $aliasRequestFilter->apply(AliasRequest::query());
        $results = $query->get();
        $this->assertTrue($results->contains($aliasRequestLocalPart));
        $this->assertTrue($results->contains($aliasRequestDomain));
        $this->assertFalse($results->contains($otherAliasRequest));
        $this->assertTrue($results->count() === 2);
    }

    public function testSearchWithoutKeyword()
    {
        $aliasRequest1 = factory(AliasRequest::class)->create();
        $aliasRequest2 = factory(AliasRequest::class)->create();

        $aliasRequestFilter = $this->createAliasRequestFilter(['search' => '']);
        $query = $aliasRequestFilter->apply(AliasRequest::query());
        $results = $query->get();
        $this->assertTrue($results->contains($aliasRequest1));
        $this->assertTrue($results->contains($aliasRequest2));
        $this->assertTrue($results->count() === 2);
    }
}
