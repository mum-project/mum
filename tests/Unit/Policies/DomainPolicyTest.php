<?php

namespace Tests\Unit\Policies;

use App\Domain;
use App\Mailbox;
use App\Policies\DomainPolicy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomainPolicyTest extends TestCase
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
        $policy = new DomainPolicy();
        $this->assertTrue($policy->before($admin, null));
        $this->assertNull($policy->before($mailbox, null));
    }

    public function testView()
    {
        $mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
        $domain = Domain::factory()->create();
        $policy = new DomainPolicy();
        $this->assertFalse($policy->view($mailbox, $domain));
    }

    public function testCreate()
    {
        $mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
        $policy = new DomainPolicy();
        $this->assertFalse($policy->create($mailbox));
    }

    public function testUpdate()
    {
        $mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
        $domain = Domain::factory()->create();
        $policy = new DomainPolicy();
        $this->assertFalse($policy->update($mailbox, $domain));
    }

    public function testDelete()
    {
        $mailbox = Mailbox::factory()->create(['is_super_admin' => false]);
        $domain = Domain::factory()->create();
        $policy = new DomainPolicy();
        $this->assertFalse($policy->delete($mailbox, $domain));
    }
}
