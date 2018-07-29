<?php

namespace App\Http\Controllers\Auth;

use App\Domain;
use App\Http\Controllers\Controller;
use function getDomainOfEmailAddress;
use function getLocalPartOfEmailAddress;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $domain = Domain::where('domain', getDomainOfEmailAddress($request->get('email')))->first();
        return [
            'local_part' => getLocalPartOfEmailAddress($request->get('email')),
            'domain_id'  => $domain ? $domain->id : null,
            'password'   => $request->get('password'),
            'active'     => 1
        ];
    }
}
