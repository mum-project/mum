<?php

namespace Tests\Unit\Policies;

use App\Domain;
use App\Mailbox;
use App\Policies\TlsPolicyPolicy;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TlsPolicyPolicyTest extends TestCase
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
        $policy = new TlsPolicyPolicy();
        $this->assertTrue($policy->before($admin, null));
        $this->assertNull($policy->before($mailbox, null));
    }
}
