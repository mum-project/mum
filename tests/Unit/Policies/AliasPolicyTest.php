<?php

namespace Tests\Unit\Policies;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\Policies\AliasPolicy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AliasPolicyTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
    }

    public function testBefore()
    {
        $admin = Mailbox::factory()->create(['is_super_admin' => true]);
        $mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
        $policy = new AliasPolicy();
        $this->assertTrue($policy->before($admin, null));
        $this->assertNull($policy->before($mailbox, null));
    }

    public function testView()
    {
        $mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
        $otherMailbox = Mailbox::factory()->create(['is_super_admin' => false]);
        /** @var Alias $alias */
        $alias = Alias::factory()->create();
        $alias->addRecipientMailbox($mailbox);
        $policy = new AliasPolicy();
        $this->assertTrue($policy->view($mailbox, $alias));
        $this->assertFalse($policy->view($otherMailbox, $alias));
    }
}
