@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Mailboxes' => route('mailboxes.index'), $mailbox->domain->domain => route('mailboxes.index', ['domain' => $mailbox->domain]), $mailbox->local_part => route('mailboxes.show', compact('mailbox')), 'Edit'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('mailboxes.update', compact('mailbox')) }}" method="POST">
                @csrf
                @method('PATCH')
                <h2 class="mb-6 font-extrabold break-words">Edit Mailbox</h2>
                <div class="form-row">
                    <label class="mb-3 form-label">Email Address</label>
                    <div class="mb-3">{{ $mailbox->address() }}</div>
                    <p class="form-help">For technical reasons, you cannot change the local part or the domain of a
                        mailbox once it is created.</p>
                </div>
                @if(config('mum.mailboxes.forename_activated'))
                    <div class="form-multi-row">
                        <div class="form-group md:w-1/2">
                            @component('layout.components.input')
                                @slot('name', 'forename')
                                @slot('placeholder', 'Jon')
                                @slot('value', $mailbox->forename)
                                The forename of the person that will use this account.
                            @endcomponent
                        </div>
                        <div class="form-group md:w-1/2">
                            @component('layout.components.input')
                                @slot('name', 'name')
                                @slot('placeholder', 'Doe')
                                @slot('value', $mailbox->name)
                                The name of the person or software that will use this account.
                            @endcomponent
                        </div>
                    </div>
                @else
                    <div class="form-row max-w-sm">
                        @component('layout.components.input')
                            @slot('name', 'name')
                            @slot('placeholder', 'Jon Doe')
                            @slot('value', $mailbox->name)
                            The name of the person or software that will use this account.
                        @endcomponent
                    </div>
                @endif
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.passwordInput')
                            @slot('name', 'password')
                            @slot('label', 'New Password')
                            @slot('minlength', config('auth.password_min_length'))
                            Leave blank if you don't want to change the password.<br>
                            The minimum length is {{ config('auth.password_min_length') }} characters.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.passwordInput')
                            @slot('name', 'new_password_confirmation')
                            @slot('label', 'Confirm Password')
                            @slot('minlength', config('auth.password_min_length'))
                            Leave blank if you don't want to change the password.<br>
                            To eliminate typing errors, please confirm the password.
                        @endcomponent
                    </div>
                </div>
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'alternative_email')
                            @slot('label', 'Alternative Email')
                            @slot('type', 'email')
                            @slot('placeholder', 'doe@example.com')
                            @slot('value', $mailbox->alternative_email)
                            If the user has an alternative email address, we can use it for password resets.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'quota')
                            @slot('type', 'number')
                            @slot('extraProps', 'step="1" min="0"')
                            @slot('addon', 'GB')
                            @slot('value', $mailbox->quota)
                            Maximum storage space this mailbox may use. Leave blank for no limit.
                        @endcomponent
                    </div>
                </div>
                <div class="form-multi-row">
                    <div class="form-group {{ Auth::user()->isSuperAdmin() ? 'md:w-1/3' : 'md:w-1/2' }}">
                        @component('layout.components.checkbox')
                            @slot('name', 'active')
                            @slot('label', 'Active')
                            @slot('checked', $mailbox->active)
                            Should the account be able to login?
                        @endcomponent
                    </div>
                    <div class="form-group {{ Auth::user()->isSuperAdmin() ? 'md:w-1/3' : 'md:w-1/2' }}">
                        @component('layout.components.checkbox')
                            @slot('name', 'send_only')
                            @slot('label', 'Send Only')
                            @slot('checked', $mailbox->send_only)
                            Should the account be restricted from receiving emails?
                        @endcomponent
                    </div>
                    @if (Auth::user()->isSuperAdmin())
                        <div class="form-group md:w-1/3">
                            @component('layout.components.checkbox')
                                @slot('name', 'is_super_admin')
                                @slot('label', 'Super Admin')
                                @slot('checked', $mailbox->is_super_admin)
                                Should the account be a super admin? Leave unchecked for a normal user.
                            @endcomponent
                        </div>
                    @endif
                </div>
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                    <a class="ml-auto btn-link hover:text-red" href="#" @click.prevent="showPopupModal = true">Delete</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('modal')
    @component('layout.components.deleteModal')
        @slot('name', $mailbox->address())
        @slot('route', route('mailboxes.destroy', compact('mailbox')))
    @endcomponent
@endsection
