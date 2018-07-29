<?php

namespace App\Http\Controllers\Auth;

use App\Rules\OwnPassword;
use function compact;
use function config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function redirect;

class ChangePasswordController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPasswordChangeForm()
    {
        return response()->view('auth.passwords.change', compact('mailbox'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => [
                'required',
                'string',
                new OwnPassword
            ],
            'password'     => 'required|string|confirmed|min:' . config('auth.password_min_length')
        ]);
        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->back()->with('success', 'Your password was changed successfully.');
    }
}
