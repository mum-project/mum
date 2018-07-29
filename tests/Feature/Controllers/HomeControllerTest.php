<?php

namespace Tests\Feature\Controllers;

use App\Domain;
use App\Mailbox;
use function factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        factory(Domain::class)->create();
    }

    public function testIndex()
    {
        $mailbox = factory(Mailbox::class)->create();
        $this->get(route('home'))->assertRedirect();
        $this->actingAs($mailbox)->get(route('home'))->assertStatus(200);
    }
}
