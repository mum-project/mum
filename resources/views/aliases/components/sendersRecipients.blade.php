{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('aliases.components.sendersRecipients')
    |     @slot('senderAndRecipientMailboxes', $senderAndRecipientMailboxes)
    |     @slot('senderMailboxes', $senderMailboxes)
    |     @slot('recipientMailboxes', $recipientMailboxes)
    |     @slot('externalRecipients', $externalRecipients)
    | @endcomponent                                ^------------- all required
    |
--}}
@if ($senderAndRecipientMailboxes->isNotEmpty())
    <div class="flex flex-col mb-8">
        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
            Sender and Recipient Mailboxes
        </div>
        <div class="-mx-1 flex flex-row flex-wrap">
            @if ($senderAndRecipientMailboxes->isEmpty())
                <div class="mx-1">&mdash;</div>
            @endif
            @foreach($senderAndRecipientMailboxes as $senderMailbox)
                <a class="address-pill link group"
                   href="{{ route('mailboxes.show', ['mailbox' => $senderMailbox]) }}">
                    <i class="fas fa-inbox text-grey group-hover:text-grey-darker mr-2"></i>{{ $senderMailbox->address() }}
                </a>
            @endforeach
        </div>
    </div>
@endif
@if ($senderMailboxes->isNotEmpty() || $senderAndRecipientMailboxes->isEmpty() && $externalRecipients->isEmpty())
    <div class="flex flex-col mb-8">
        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
            Sender Mailboxes
        </div>
        <div class="-mx-1 flex flex-row flex-wrap">
            @if ($senderMailboxes->isEmpty())
                <div class="mx-1">&mdash;</div>
            @endif
            @foreach($senderMailboxes as $senderMailbox)
                <a class="address-pill link group"
                   href="{{ route('mailboxes.show', ['mailbox' => $senderMailbox]) }}">
                    <i class="fas fa-inbox text-grey group-hover:text-grey-darker mr-2"></i>{{ $senderMailbox->address() }}
                </a>
            @endforeach
        </div>
    </div>
@endif
@if ($recipientMailboxes->isNotEmpty() || $senderAndRecipientMailboxes->isEmpty() && $externalRecipients->isEmpty())
    <div class="flex flex-col mb-8">
        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
            Recipient Mailboxes
        </div>
        <div class="-mx-1 flex flex-row flex-wrap">
            @if ($recipientMailboxes->isEmpty())
                <div class="mx-1">&mdash;</div>
            @endif
            @foreach($recipientMailboxes as $mailbox)
                <a class="address-pill link group"
                   href="{{ route('mailboxes.show', ['mailbox' => $mailbox->id]) }}">
                    <i class="fas fa-inbox text-grey group-hover:text-grey-darker mr-2"></i>{{ $mailbox->address() }}
                </a>
            @endforeach
        </div>
    </div>
@endif
@if ($externalRecipients->isNotEmpty())
    <div class="flex flex-col mb-8">
        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
            External Recipients
        </div>
        <div class="-mx-1 flex flex-row flex-wrap">
            @foreach($externalRecipients as $recipient)
                <div class="address-pill">
                    {{ $recipient->recipient_address }}
                </div>
            @endforeach
        </div>
    </div>
@endif