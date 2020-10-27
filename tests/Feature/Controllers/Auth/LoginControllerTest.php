<?php

namespace Tests\Feature\Controllers\Auth;

use App\Domain;
use App\Mailbox;
use function csrf_token;
use function factory;
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
        $this->followingRedirects()
            ->post(route('login'), [
                'email'    => 'foobar',
                'password' => 'secret',
                '_token'   => csrf_token()
            ])
            ->assertSeeText(trans('validation.email', ['attribute' => 'email']));
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
