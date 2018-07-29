<?php

namespace Tests\Unit\Policies;

use App\Domain;
use App\Mailbox;
use App\Policies\DomainPolicy;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomainPolicyTest extends TestCase
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
        $policy = new DomainPolicy();
        $this->assertTrue($policy->before($admin, null));
        $this->assertNull($policy->before($mailbox, null));
    }

    public function testView()
    {
        $mailbox = factory(Mailbox::class)->create(['is_super_admin' => false]);
        $domain = factory(Domain::class)->create();
        $policy = new DomainPolicy();
        $this->assertFalse($policy->view($mailbox, $domain));
    }

    public function testCreate()
    {
        $mailbox = factory(Mailbox::class)->create(['is_super_admin' => false]);
        $policy = new DomainPolicy();
        $this->assertFalse($policy->create($mailbox));
    }

    public function testUpdate()
    {
        $mailbox = factory(Mailbox::class)->create(['is_super_admin' => false]);
        $domain = factory(Domain::class)->create();
        $policy = new DomainPolicy();
        $this->assertFalse($policy->update($mailbox, $domain));
    }

    public function testDelete()
    {
        $mailbox = factory(Mailbox::class)->create(['is_super_admin' => false]);
        $domain = factory(Domain::class)->create();
        $policy = new DomainPolicy();
        $this->assertFalse($policy->delete($mailbox, $domain));
    }
}
