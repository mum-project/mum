@extends('layout.master')

@section('content')
        <div class="max-w-lg">
            <div class="dashboard-tile">
                <h2 class="font-extrabold">Change Your Password</h2>
                <div class="mt-6">
                    <form method="POST" action="{{ route('password.change') }}">
                        @csrf
                        <div class="flex flex-col">
                            <div class="form-multi-row">
                                <div class="form-group w-1/2">
                                    @component('layout.components.passwordInput')
                                        @slot('name', 'old_password')
                                        @slot('label', 'Old Password')
                                        @slot('required', true)
                                        @slot('minlength', config('auth.password_min_length'))
                                        To make sure you really are the logged in user, we need you to type in your
                                        current
                                        password.
                                    @endcomponent
                                </div>
                            </div>
                            <div class="form-multi-row">
                                <div class="form-group w-1/2">
                                    @component('layout.components.passwordInput')
                                        @slot('name', 'password')
                                        @slot('label', 'New Password')
                                        @slot('minlength', config('auth.password_min_length'))
                                        @slot('required', true)
                                        Use whatever characters you like and make it as long as possible.
                                        The minimum length is {{ config('auth.password_min_length') }} characters.
                                    @endcomponent
                                </div>
                                <div class="form-group w-1/2">
                                    @component('layout.components.passwordInput')
                                        @slot('name', 'password_confirmation')
                                        @slot('label', 'Confirm New Password')
                                        @slot('required', true)
                                        @slot('minlength', config('auth.password_min_length'))
                                        To eliminate typing errors, please confirm your new password.
                                    @endcomponent
                                </div>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                                <a href="{{ url()->previous() }}" class="md:mr-auto md:ml-4 btn-link">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection