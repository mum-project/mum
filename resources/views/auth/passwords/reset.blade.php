@extends('auth.master')

@section('content')
    <div class="bg-white py-6 px-6 rounded shadow-md max-w-sm w-full mx-auto">
        <div class="mb-6">
            <h2 class="font-extrabold">{{ __('Reset Your Password') }}</h2>
        </div>
        @include('layout.messages')
        <div>
            <div class="flex flex-row mb-6">
                <i class="fas fa-question-circle text-3xl mr-3 mt-1 text-grey"></i>
                <div class="leading-normal text-sm">
                    Please enter your email address and provide a new password that you want to use from now on.
                    To make sure that we don't save a typo, please confirm your password.
                </div>
            </div>
            <form method="POST" action="{{ route('password.request') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="flex flex-col sm:flex-row sm:items-center mb-3">
                    <label class="sm:w-1/3 mb-3 sm:mb-0 text-grey-darker pr-1" for="email">{{ __('Email') }}</label>
                    <div class="sm:w-2/3 flex flex-col">
                        <input class="form-input{{ $errors->has('email') ? ' border-red' : '' }}" name="email"
                               id="email" type="email" value="{{ $email ?? old('email') }}"
                               placeholder="you@example.com" required autofocus>
                        @if ($errors->has('email'))
                            <div class="mt-1 form-help text-red">{{ $errors->first('email') }}</div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center mb-3">
                    <label class="sm:w-1/3 mb-3 sm:mb-0 text-grey-darker pr-1" for="password">{{ __('New Password') }}</label>
                    <div class="sm:w-2/3 flex flex-col">
                        <input class="form-input{{ $errors->has('password') ? ' border-red' : '' }}" name="password"
                               id="password" type="password" required>
                        @if ($errors->has('password'))
                            <div class="mt-1 form-help text-red">{{ $errors->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center mb-6">
                    <label class="sm:w-1/3 mb-3 sm:mb-0 text-grey-darker pr-1" for="password-confirm">{{ __('Confirm Password') }}</label>
                    <div class="sm:w-2/3 flex flex-col">
                        <input class="form-input{{ $errors->has('password_confirmation') ? ' border-red' : '' }}"
                               name="password_confirmation"
                               id="password-confirm" type="password" required>
                        @if ($errors->has('password_confirmation'))
                            <div class="mt-1 form-help text-red">{{ $errors->first('password_confirmation') }}</div>
                        @endif
                    </div>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn bg-blue text-white">{{ __('Reset Password') }}</button>
                    <a href="{{ route('login') }}" class="btn-link">Login</a>
                </div>
            </form>
        </div>
    </div>
@endsection
