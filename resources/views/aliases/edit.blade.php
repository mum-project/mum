@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Aliases' => route('aliases.index'), $alias->domain->domain => route('aliases.index', ['domain' => $alias->domain]), $alias->local_part => route('aliases.show', compact('alias')), 'Edit'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('aliases.update', compact('alias')) }}" method="POST">
                @csrf
                @method('PATCH')
                <h2 class="mb-6 font-extrabold break-words">Edit Alias</h2>
                <div class="form-multi-row">
                    <div class="form-group w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'local_part')
                            @slot('label', 'User')
                            @slot('placeholder', 'jon.doe')
                            @slot('addon', '@')
                            @slot('align', 'left')
                            @slot('required', true)
                            @slot('value', $alias->local_part)
                            This is the local part of the email address.
                        @endcomponent
                    </div>
                    <div class="form-group w-1/2">
                        @component('layout.components.select')
                            @slot('name', 'domain_id')
                            @slot('label', 'domain')
                            @slot('required', true)
                            @slot('options', $availableDomainOptions)
                            @slot('placeholder', ' ')
                            @slot('selected', $alias->domain_id)
                            Please choose a domain or create a new one
                            <a class="link-black" href="{{ route('domains.create') }}">here</a>.
                        @endcomponent
                    </div>
                </div>
                <div class="form-row">
                    @component('layout.components.textarea')
                        @slot('name', 'description')
                        @slot('value', $alias->description)
                        Maybe something to help you to remember what this alias is used for.
                    @endcomponent
                </div>
                <div class="form-row border-t border-grey-lighter">
                    <alias-senders-recipients-form
                            class="pt-4"
                            :old-sender-mailboxes="{{ json_encode(old('sender_mailboxes') ?? $senderMailboxes) }}"
                            :old-recipient-mailboxes="{{ json_encode(old('recipient_mailboxes') ?? $recipientMailboxes) }}"
                            :old-external-recipients="{{ json_encode(old('external_recipients') ?? $externalRecipients) }}"
                            @set-modal-content-identifier="setModalContentIdentifier"
                            @set-modal-content-payload="setModalContentPayload"
                            :validation-errors-prop="{ senderMailboxes: '{{ $errors->first('sender_mailboxes') }}', recipientMailboxes: '{{ $errors->first('recipient_mailboxes') }}', externalRecipients: '{{ $errors->first('external_recipients') }}' }"
                    ></alias-senders-recipients-form>
                    @if (is_array(old('sender_mailboxes')))
                        <div class="mt-2 leading-normal">
                            @foreach(old('sender_mailboxes') as $key => $oldInput)
                                @if ($errors->has('sender_mailboxes.' . $key . '.id') && array_key_exists('address', $oldInput))
                                    <p class="form-help text-red">
                                        The sender mailbox {{ $oldInput['address'] }} is invalid.
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if (is_array(old('recipient_mailboxes')))
                        <div class="mt-2 leading-normal">
                            @foreach(old('recipient_mailboxes') as $key => $oldInput)
                                @if ($errors->has('recipient_mailboxes.' . $key . '.id') && array_key_exists('address', $oldInput))
                                    <p class="form-help text-red">
                                        The recipient mailbox {{ $oldInput['address'] }} is invalid.
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if (is_array(old('external_recipients')))
                        <div class="mt-2 leading-normal">
                            @foreach(old('external_recipients') as $key => $oldInput)
                                @if ($errors->has('external_recipients.' . $key . '.address') && array_key_exists('address', $oldInput))
                                    <p class="form-help text-red">
                                        The external email address {{ $oldInput['address'] }} is invalid.
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="form-row">
                    <alias-deactivate-at-input
                            standard-days="{{ config('mum.aliases.deactivate_at.default_days') }}"
                            standard-hours="{{ config('mum.aliases.deactivate_at.default_hours') }}"
                            standard-minutes="{{ config('mum.aliases.deactivate_at.default_minutes') }}"
                    ></alias-deactivate-at-input>
                </div>
                <div class="form-row">
                    @component('layout.components.checkbox')
                        @slot('name', 'active')
                        @slot('label', 'Active')
                        @slot('checked', $alias->active)
                        If disabled, the alias won't receive emails and can't be used as a sending address.
                    @endcomponent
                </div>
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                    @can('delete', $alias)
                        <a class="btn-link ml-auto hover:text-red" href="#"
                           @click.prevent="showPopupModal = true">Delete</a>
                    @endcan
                </div>
            </form>
        </div>
    </div>
@endsection

@section('modal')
    @component('layout.components.deleteModal')
        @slot('name', $alias->address())
        @slot('route', route('aliases.destroy', compact('alias')))
    @endcomponent
@endsection