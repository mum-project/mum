<?php

namespace App\Http\Controllers\Auth;

use App\Domain;
use App\Http\Controllers\Controller;
use App\Notifications\ResetPassword;
use function getDomainOfEmailAddress;
use function getLocalPartOfEmailAddress;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

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
     * Send a reset link to the given user.
     * This method overrides the SendsPasswordResetEmails's sendResetLinkEmail()
     * to use $this->credentials() for sendResetLink()
     * instead of $request->only('email').
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($response)
            : $this->sendResetLinkFailedResponse($request, $response);
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
