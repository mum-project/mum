<?php

namespace Tests\Feature\Console\Commands;

use App\Alias;
use App\Domain;
use App\Mailbox;
use Carbon\Carbon;
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
        $aliasesToDeactivate = Alias::factory(2)->create([
            'deactivate_at' => $deactivateAt
        ]);
        $normalAliases = Alias::factory(2)->create();

        $this->artisan('aliases:deactivate')
            ->assertExitCode(0);

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
        $this->artisan('aliases:deactivate')
            ->assertExitCode(0);
    }

    public function testDeactivateAtInFuture()
    {
        $deactivateAt = Carbon::now()
            ->addHour();
        $aliasesToDeactivateInFuture = Alias::factory(2)->create([
            'deactivate_at' => $deactivateAt
        ]);

        $this->artisan('aliases:deactivate')
            ->assertExitCode(0);

        $aliasesToDeactivateInFuture->each(function (Alias $alias) {
            $alias = $alias->fresh();
            $this->assertTrue(!!$alias->active);
            $this->assertNotNull($alias->deactivate_at);
        });
    }
}
