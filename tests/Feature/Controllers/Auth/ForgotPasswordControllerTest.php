<?php

namespace Tests\Feature\Controllers\Auth;

use App\Domain;
use App\Mailbox;
use App\Notifications\ResetPassword;
use function csrf_token;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
    }

    public function testSendResetLinkEmail()
    {
        $user = Mailbox::factory()->create(['active' => true]);
        Notification::fake();
        Session::start();

        $this->followingRedirects()
            ->post(route('password.email'), [
                'email'  => $user->address(),
                '_token' => csrf_token()
            ])
            ->assertSuccessful();

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
