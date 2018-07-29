<?php

namespace Tests\Unit\Filters;

use App\Http\Filters\SystemServiceFilter;
use App\SystemService;
use function factory;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemServiceFilterTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * Creates a QueryFilter and injects a mocked Request object
     * that supplies the provided request parameters to the filter.
     *
     * @param array $requestParams
     * @return SystemServiceFilter
     */
    protected function createSystemServiceFilter(array $requestParams)
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($requestParams);
        return new SystemServiceFilter($mockRequest);
    }

    public function testSearch()
    {
        $systemService1 = factory(SystemService::class)->create(['service' => 'foobarABC']);
        $systemService2 = factory(SystemService::class)->create(['name' => 'foobarXYZ']);
        $otherSystemService = factory(SystemService::class)->create();

        $systemServiceFilter = $this->createSystemServiceFilter(['search' => 'foobar']);
        $results = $systemServiceFilter->apply(SystemService::query())->get();

        $this->assertTrue($results->contains($systemService1));
        $this->assertTrue($results->contains($systemService2));
        $this->assertFalse($results->contains($otherSystemService));
        $this->assertTrue($results->count() === 2);
    }
}
