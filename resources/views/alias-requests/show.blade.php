@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Aliases' => route('aliases.index'), 'Requests' => route('alias-requests.index'), $aliasRequest->address()])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p">
                <div class="flex flex-col md:flex-row md:items-center mb-2">
                    <h2 class="font-extrabold md:mr-2 break-words">Request for {{ $aliasRequest->address() }}</h2>
                    <div class="flex flex-row flex-wrap mt-2 md:mt-0">
                        @if ($aliasRequest->status === 'open')
                            <div class="mx-1 my-1 tag-pill bg-blue">open</div>
                        @elseif ($aliasRequest->status === 'approved')
                            <div class="mx-1 my-1 tag-pill bg-green">approved</div>
                        @elseif ($aliasRequest->status === 'dismissed')
                            <div class="mx-1 my-1 tag-pill bg-red">dismissed</div>
                        @endif
                    </div>
                </div>
                <p class="text-xs text-grey-dark mb-8">
                    Requested {{ $aliasRequest->created_at->diffForHumans() }}
                    @if ( ! $aliasRequest->created_at->eq($aliasRequest->updated_at))
                    &middot;
                    Updated {{ $aliasRequest->updated_at->diffForHumans() }}
                    @endif
                </p>
                <div class="flex flex-col mb-8">
                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                        Requested by
                    </div>
                    <div class="">
                        @if ($aliasRequest->mailbox->name)
                            {{ $aliasRequest->mailbox->name }}
                            (<a class="link-black"
                                href="{{ route('mailboxes.show', ['mailbox' => $aliasRequest->mailbox]) }}"
                            >{{ $aliasRequest->mailbox->address() }}</a>)
                        @else
                            <a class="link-black"
                               href="{{ route('mailboxes.show', ['mailbox' => $aliasRequest->mailbox]) }}">
                                {{ $aliasRequest->mailbox->address() }}
                            </a>
                        @endif
                    </div>
                </div>
                @component('aliases.components.sendersRecipients')
                    @slot('senderAndRecipientMailboxes', $senderAndRecipientMailboxes)
                    @slot('senderMailboxes', $senderMailboxes)
                    @slot('recipientMailboxes', $recipientMailboxes)
                    @slot('externalRecipients', $externalRecipients)
                @endcomponent
            </div>
            <div class="inner-p form-footer">
                @if ($aliasRequest->status === 'approved')
                    <a class="btn-link" href="{{ route('aliases.show', ['alias' => $aliasRequest->alias->id]) }}">Show
                        Corresponding Alias</a>
                @else
                    @if (isUserSuperAdmin())
                        @if ($aliasRequest->status === 'dismissed')
                            <div>
                                <form method="POST"
                                      action="{{ route('alias-requests.status', compact('aliasRequest')) }}">
                                    @method('PATCH')
                                    @csrf
                                    <input type="hidden" name="status" value="open">
                                    <button class="btn btn-primary" type="submit">Reopen</button>
                                </form>
                            </div>
                        @else
                            <div>
                                <form method="POST"
                                      action="{{ route('alias-requests.status', compact('aliasRequest')) }}">
                                    @method('PATCH')
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-primary" type="submit">Approve</button>
                                </form>
                            </div>
                            <div class="mr-auto ml-4">
                                <form method="POST"
                                      action="{{ route('alias-requests.status', compact('aliasRequest')) }}">
                                    @method('PATCH')
                                    @csrf
                                    <input type="hidden" name="status" value="dismissed">
                                    <button class="btn-link" type="submit">Dismiss</button>
                                </form>
                            </div>
                        @endif
                    @endif
                    @if ($aliasRequest->status !== 'dismissed')
                        <a class="btn-link" href="{{ route('alias-requests.edit', compact('aliasRequest')) }}">Edit</a>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
