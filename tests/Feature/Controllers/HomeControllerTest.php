<?php

namespace Tests\Feature\Controllers;

use App\Domain;
use App\Mailbox;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
    }

    public function testIndex()
    {
        $mailbox = Mailbox::factory()->create();
        $this->get(route('home'))->assertRedirect();
        $this->actingAs($mailbox)->get(route('home'))->assertStatus(200);
    }
}
