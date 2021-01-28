<?php

namespace Tests\Feature\Controllers\Auth;

use App\Domain;
use App\Mailbox;
use function csrf_token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChangePasswordControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
    }

    public function testShowPasswordChangeForm()
    {
        $this->get(route('password.change'))
            ->assertSuccessful();
    }

    public function testUpdatePassword()
    {
        $oldPass = Str::random(10);
        $newPass = Str::random(10);
        $mailbox = Mailbox::factory()->create([
            'password' => Hash::make($oldPass),
            'active'   => true
        ]);
        Session::start();
        $this->assertFalse(Auth::check());
        Auth::login($mailbox);
        $this->followingRedirects()
            ->post(route('password.change'), [
                'old_password'          => $oldPass,
                'password'              => $newPass,
                'password_confirmation' => $newPass,
                '_token'                => csrf_token()
            ])
            ->assertSuccessful()
            ->assertDontSee('password_confirmation');
        $this->assertTrue(Auth::check());
        $this->followingRedirects()
            ->get(route('logout'))
            ->assertSuccessful();
        $this->assertFalse(Auth::check());
        $this->followingRedirects()
            ->post(route('login'), [
                'email'    => $mailbox->address(),
                'password' => $newPass,
                '_token'   => csrf_token()
            ])
            ->assertSuccessful();
        $this->assertTrue(Auth::check());
    }
}
