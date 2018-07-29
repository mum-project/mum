<?php

namespace Tests\Feature\Console\Commands;

use App\Alias;
use App\Domain;
use App\Mailbox;
use Carbon\Carbon;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeactivateAliasesTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
        factory(Mailbox::class)->create();
    }

    public function testDeactivatingAliases()
    {
        $deactivateAt = Carbon::now()
            ->subMinute();
        $aliasesToDeactivate = factory(Alias::class, 2)->create([
            'deactivate_at' => $deactivateAt
        ]);
        $normalAliases = factory(Alias::class, 2)->create();

        $returnCode = $this->artisan('aliases:deactivate');
        $this->assertEquals(0, $returnCode);

        $aliasesToDeactivate->each(function (Alias $alias) use ($deactivateAt) {
            $alias = $alias->fresh();
            $this->assertTrue(!$alias->active);
            $this->assertNotNull($alias->deactivate_at);
        });

        $normalAliases->each(function (Alias $alias) {
            $alias = $alias->fresh();
            $this->assertTrue(!!$alias->active);
            $this->assertNull($alias->deactivate_at);
        });
    }

    public function testNoAliases()
    {
        $this->assertEquals(0, Alias::query()
            ->count());
        $returnCode = $this->artisan('aliases:deactivate');
        $this->assertEquals(0, $returnCode);
    }

    public function testDeactivateAtInFuture()
    {
        $deactivateAt = Carbon::now()
            ->addHour();
        $aliasesToDeactivateInFuture = factory(Alias::class, 2)->create([
            'deactivate_at' => $deactivateAt
        ]);

        $returnCode = $this->artisan('aliases:deactivate');
        $this->assertEquals(0, $returnCode);

        $aliasesToDeactivateInFuture->each(function (Alias $alias) {
            $alias = $alias->fresh();
            $this->assertTrue(!!$alias->active);
            $this->assertNotNull($alias->deactivate_at);
        });
    }
}
