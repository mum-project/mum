@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Mailboxes' => route('mailboxes.index'), 'Create'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('mailboxes.store') }}" method="POST">
                @csrf
                <h2 class="mb-6 font-extrabold break-words">Create Mailbox</h2>
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'local_part')
                            @slot('label', 'User')
                            @slot('placeholder', 'jon.doe')
                            @slot('addon', '@')
                            @slot('required', true)
                            @slot('align', 'left')
                            This is the local part of the email address.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.select')
                            @slot('name', 'domain_id')
                            @slot('label', 'domain')
                            @slot('required', true)
                            @slot('options', $availableDomainOptions)
                            @slot('placeholder', ' ')
                            @if ($domainId = request()->get('domain'))
                                @slot('selected', $domainId)
                            @endif
                            Please choose a domain or create a new one
                            <a class="link-black" href="{{ route('domains.create') }}">here</a>.
                        @endcomponent
                    </div>
                </div>
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'name')
                            @slot('placeholder', 'Jon Doe')
                            The name of the person or software that will use this account.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'quota')
                            @slot('type', 'number')
                            @slot('extraProps', 'step="1" min="0"')
                            @slot('addon', 'GB')
                            Maximum storage space this mailbox may use. Leave blank for no limit.
                        @endcomponent
                    </div>
                </div>
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.passwordInput')
                            @slot('name', 'password')
                            @slot('required', true)
                            @slot('minlength', config('auth.password_min_length'))
                            Use whatever characters you like and make it as long as possible.
                            The minimum length is {{ config('auth.password_min_length') }} characters.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.passwordInput')
                            @slot('name', 'password_confirmation')
                            @slot('label', 'Confirm Password')
                            @slot('required', true)
                            @slot('minlength', config('auth.password_min_length'))
                            To eliminate typing errors, please confirm the password.
                        @endcomponent
                    </div>
                </div>
                <div class="form-row max-w-sm">
                    @component('layout.components.input')
                        @slot('name', 'alternative_email')
                        @slot('label', 'Alternative Email')
                        @slot('type', 'email')
                        @slot('placeholder', 'doe@example.com')
                        If the user has an alternative email address, we can use it for password resets.
                    @endcomponent
                </div>
                <div class="form-multi-row">
                    <div class="form-group {{ Auth::user()->isSuperAdmin() ? 'md:w-1/3' : 'md:w-1/2' }}">
                        @component('layout.components.checkbox')
                            @slot('name', 'active')
                            @slot('label', 'Active')
                            @slot('checked', true)
                            Should the account be able to login?
                        @endcomponent
                    </div>
                    <div class="form-group {{ Auth::user()->isSuperAdmin() ? 'md:w-1/3' : 'md:w-1/2' }}">
                        @component('layout.components.checkbox')
                            @slot('name', 'send_only')
                            @slot('label', 'Send Only')
                            Should the account be restricted from receiving emails?
                        @endcomponent
                    </div>
                    @if (Auth::user()->isSuperAdmin())
                        <div class="form-group md:w-1/3">
                            @component('layout.components.checkbox')
                                @slot('name', 'is_super_admin')
                                @slot('label', 'Super Admin')
                                Should the account be a super admin? Leave unchecked for a normal user.
                            @endcomponent
                        </div>
                    @endif
                </div>
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Create</button>
                    <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection