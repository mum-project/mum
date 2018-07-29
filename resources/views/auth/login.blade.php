@extends('auth.master')

@section('content')
    <div class="bg-white py-6 px-6 rounded shadow-md max-w-sm w-full mx-auto">
        <div class="mb-6">
            <h2 class="font-extrabold">{{ __('Login') }} to {{ config('app.name') }}</h2>
        </div>
        @include('layout.messages')
        <div class="pt-3">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="flex flex-col sm:flex-row sm:items-center mb-3">
                    <label class="sm:w-1/3 mb-3 sm:mb-0 text-grey-darker pr-1" for="email">{{ __('Email') }}</label>
                    <div class="sm:w-2/3 flex flex-col">
                        <input class="form-input{{ $errors->has('email') ? ' border-red' : '' }}" name="email"
                               id="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required
                               autofocus>
                        @if ($errors->has('email'))
                            <div class="mt-1 form-help text-red">{{ $errors->first('email') }}</div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center mb-3">
                    <label class="sm:w-1/3 mb-3 sm:mb-0 text-grey-darker pr-1" for="password">{{ __('Password') }}</label>
                    <div class="sm:w-2/3 flex flex-col">
                        <custom-password class="{{ $errors->has('password') ? 'border-red' : '' }}" name="password"
                                         id="password" required></custom-password>
                        @if ($errors->has('password'))
                            <div class="mt-1 form-help text-red">{{ $errors->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center mb-6">
                    <div class="hidden sm:block sm:w-1/3"></div>
                    <label class="sm:w-2/3 checkbox-label">
                        {{ __('Remember Me') }}
                        <input type="checkbox"
                               name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn bg-blue text-white">Login</button>
                    <a href="{{ route('password.request') }}"
                       class="btn-link">{{ __('Forgot Your Password?') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
