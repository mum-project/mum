<?php

namespace Tests\Feature\Controllers\Auth;

use App\Domain;
use App\Mailbox;
use function csrf_token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function trans;

class LoginControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
        Mailbox::factory()->create();
    }

    public function testValidateLoginInvalidEmail()
    {
        Session::start();
        $this->get(route('login'));
        $this->followingRedirects()
            ->post(route('login'), [
                'email'    => 'foobar@example.com',
                'password' => 'secret',
                '_token'   => csrf_token()
            ])
            ->assertSeeText(trans('auth.failed'));
    }

    public function testSuccessfulLogin()
    {
        $mailbox = Mailbox::factory()->create(['active' => true]);
        Session::start();
        $this->assertNull(Auth::user());
        $this->followingRedirects()
            ->post(route('login'), [
                'email'    => $mailbox->address(),
                'password' => 'secret',
                '_token'   => csrf_token()
            ])
            ->assertSuccessful();
        $this->assertNotNull(Auth::user());
    }
}
