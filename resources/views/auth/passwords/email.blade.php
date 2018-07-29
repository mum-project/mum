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
                    We will send you a link to reset your current password within the next few minutes.
                    If you can't find our email, remember to check your spam folder first.
                    If you specified an alternative email address for your account,
                    we will send the link to that address instead of your account's address.
                </div>
            </div>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="flex flex-col sm:flex-row sm:items-center mb-6">
                    <label class="sm:w-1/3 mb-3 sm:mb-0 text-grey-darker pr-1" for="email">{{ __('Email') }}</label>
                    <div class="sm:w-2/3 flex flex-col">
                        <input class="form-input{{ $errors->has('email') ? ' border-red' : '' }}" name="email"
                               id="email" type="email" value="{{ old('email') }}"placeholder="you@example.com" required>
                        @if ($errors->has('email'))
                            <div class="mt-1 form-help text-red">{{ $errors->first('email') }}</div>
                        @endif
                    </div>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn bg-blue text-white">{{ __('Send the Reset Link') }}</button>
                    <a href="{{ route('login') }}" class="btn-link">Login</a>
                </div>
            </form>
        </div>
    </div>
@endsection
