<?php

namespace App\Http\Controllers\Auth;

use App\Domain;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use function getLocalPartOfEmailAddress;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $domain = Domain::where('domain', getDomainOfEmailAddress($request->get('email')))->first();
        return [
            'local_part'            => getLocalPartOfEmailAddress($request->get('email')),
            'domain_id'             => $domain ? $domain->id : null,
            'password'              => $request->get('password'),
            'password_confirmation' => $request->get('password_confirmation'),
            'token'                 => $request->get('token'),
            'active'                => 1
        ];
    }
}
