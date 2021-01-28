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

class ResetPasswordControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Domain::factory()->create();
    }

    public function testCredentials()
    {
        $user = Mailbox::factory()->create(['active' => true]);
        $token = app('auth.password.broker')->createToken($user);
        $newPassword = $this->faker->sentence;
        $credentials = [
            'local_part' => $user->local_part,
            'domain_id'  => $user->domain->id,
            'password'   => $newPassword,
            'active'     => 1
        ];
        Session::start();

        $this->assertFalse(Auth::attempt($credentials));

        $this->followingRedirects()
            ->post(route('password.request'), [
                'email'                 => $user->address(),
                'token'                 => $token,
                'password'              => $newPassword,
                'password_confirmation' => $newPassword,
                '_token'                => csrf_token()
            ])
            ->assertSuccessful();

        $this->assertTrue(Auth::attempt($credentials));
    }
}
