@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Aliases' => route('aliases.index'), 'Requests' => route('alias-requests.index'), $aliasRequest->address() => route('alias-requests.show', compact('aliasRequest')), 'Edit'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('alias-requests.update', compact('aliasRequest')) }}" method="POST">
                @method('PATCH')
                @csrf
                <h2 class="mb-6 font-extrabold break-words">Edit Request #{{ $aliasRequest->id }}</h2>
                <div class="form-multi-row">
                    <div class="form-group w-1/2">
                        @component('layout.components.inputWithRandomGenerator')
                            @slot('name', 'local_part')
                            @slot('label', 'User')
                            @slot('placeholder', 'jon.doe')
                            @slot('addon', '@')
                            @slot('required', true)
                            @slot('align', 'left')
                            @slot('value', old('local_part') ?? $aliasRequest->local_part)
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
                            @slot('selected', old('domain_id') ?? $aliasRequest->domain_id)
                            Please choose a domain.
                        @endcomponent
                    </div>
                </div>
                <div class="form-row">
                    @component('layout.components.textarea')
                        @slot('name', 'description')
                        @slot('value', old('description') ?? $aliasRequest->description)
                        Maybe something to help you to remember what this alias is used for.
                    @endcomponent
                </div>
                <div class="form-row border-t border-b border-grey-lighter">
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
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                    @can('delete', $aliasRequest)
                        <a class="btn-link ml-auto hover:text-red" href="#"
                           @click.prevent="showPopupModal = true">Delete</a>
                    @endcan
                </div>
            </form>
        </div>
    </div>
@endsection
@section('modal')
    @can('delete', $aliasRequest)
        @component('layout.components.deleteModal')
            @slot('name', 'the alias request for "<span class="font-bold">' . $aliasRequest->address() . '</span>"')
            @slot('route', route('alias-requests.destroy', compact('aliasRequest')))
            @slot('noNameFormatting', true)
            @slot('unsafeHtml', true)
        @endcomponent
    @endcan
@endsection
