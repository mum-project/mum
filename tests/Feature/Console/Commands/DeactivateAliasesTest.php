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

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
        Mailbox::factory()->create();
    }

    public function testDeactivatingAliases()
    {
        $deactivateAt = Carbon::now()
            ->subMinute();
        $aliasesToDeactivate = Alias::factory( 2)->create([
            'deactivate_at' => $deactivateAt
        ]);
        $normalAliases = Alias::factory( 2)->create();

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
        $aliasesToDeactivateInFuture = Alias::factory( 2)->create([
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
