<?php

namespace Tests\Unit\Policies;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\Policies\AliasPolicy;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AliasPolicyTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
    }

    public function testBefore()
    {
        $admin = factory(Mailbox::class)->create(['is_super_admin' => true]);
        $mailbox = factory(Mailbox::class)->create(['is_super_admin' => false]);
        $policy = new AliasPolicy();
        $this->assertTrue($policy->before($admin, null));
        $this->assertNull($policy->before($mailbox, null));
    }

    public function testView()
    {
        $mailbox = factory(Mailbox::class)->create(['is_super_admin' => false]);
        $otherMailbox = factory(Mailbox::class)->create(['is_super_admin' => false]);
        /** @var Alias $alias */
        $alias = factory(Alias::class)->create();
        $alias->addRecipientMailbox($mailbox);
        $policy = new AliasPolicy();
        $this->assertTrue($policy->view($mailbox, $alias));
        $this->assertFalse($policy->view($otherMailbox, $alias));
    }
}
