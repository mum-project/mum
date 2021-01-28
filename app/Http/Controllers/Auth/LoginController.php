<?php

namespace App\Http\Controllers\Auth;

use App\Domain;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use function getDomainOfEmailAddress;
use function getLocalPartOfEmailAddress;

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
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request): array
    {
        $domain = Domain::query()
            ->where('domain', getDomainOfEmailAddress($request->get('email')))
            ->first();

        return [
            'local_part' => getLocalPartOfEmailAddress($request->get('email')),
            'domain_id'  => $domain ? $domain->id : null,
            'password'   => $request->get('password'),
            'active'     => 1
        ];
    }
}
