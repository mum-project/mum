@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Aliases' => route('aliases.index'), 'Requests' => route('alias-requests.index'), 'Create'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('alias-requests.store') }}" method="POST">
                @csrf
                <h2 class="mb-6 font-extrabold break-words">Request a new Alias</h2>
                <div class="form-multi-row">
                    <div class="form-group w-1/2">
                        @component('layout.components.inputWithRandomGenerator')
                            @slot('name', 'local_part')
                            @slot('label', 'User')
                            @slot('placeholder', 'jon.doe')
                            @slot('addon', '@')
                            @slot('required', true)
                            @slot('align', 'left')
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
                            @if ($domainId = request()->get('domain'))
                                @slot('selected', $domainId)
                            @endif
                            Please choose a domain.
                        @endcomponent
                    </div>
                </div>
                <div class="form-row">
                    @component('layout.components.textarea')
                        @slot('name', 'description')
                        Maybe something to help you to remember what this alias is used for.
                    @endcomponent
                </div>
                <div class="form-row border-t border-b border-grey-lighter">
                    <alias-senders-recipients-form
                            class="py-4"
                            @if (is_array(old('sender_mailboxes')))
                            :old-sender-mailboxes="{{ json_encode(old('sender_mailboxes')) }}"
                            @endif
                            @if (is_array(old('recipient_mailboxes')))
                            :old-recipient-mailboxes="{{ json_encode(old('recipient_mailboxes')) }}"
                            @endif
                            @if (is_array(old('external_recipients')))
                            :old-external-recipients="{{ json_encode(old('external_recipients')) }}"
                            @endif
                            @set-modal-content-identifier="setModalContentIdentifier"
                            @set-modal-content-payload="setModalContentPayload"
                            :validation-errors-prop="{ senderMailboxes: '{{ $errors->first('sender_mailboxes') }}', recipientMailboxes: '{{ $errors->first('recipient_mailboxes') }}', externalRecipients: '{{ $errors->first('external_recipients') }}' }"
                    ></alias-senders-recipients-form>
                    @if (is_array(old('sender_mailboxes')))
                        <div class="mt-2 leading-normal">
                            @foreach(old('sender_mailboxes') as $key => $oldInput)
                                @if ($errors->has('sender_mailboxes.' . $key . '.id'))
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
                                @if ($errors->has('recipient_mailboxes.' . $key . '.id'))
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
                                @if ($errors->has('external_recipients.' . $key . '.address'))
                                    <p class="form-help text-red">
                                        The external email address {{ $oldInput['address'] }} is invalid.
                                    </p>
                                @endif
                            @endforeach
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