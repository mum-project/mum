<?php

namespace Tests\Unit\Policies;

use App\Domain;
use App\Mailbox;
use App\Policies\MailboxPolicy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailboxPolicyTest extends TestCase
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
        $policy = new MailboxPolicy();
        $this->assertTrue($policy->before($admin, null));
        $this->assertNull($policy->before($mailbox, null));
    }
}
