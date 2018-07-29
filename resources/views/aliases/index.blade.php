@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @if (app('request')->input('domain') && $domain = App\Domain::find(app('request')->input('domain')))
            @slot('links', ['Aliases' => route('aliases.index'), $domain->domain])
        @else
            @slot('links', ['Aliases'])
        @endif
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p flex flex-col border-grey-lighter border-b">
                <div class="flex flex-row items-center">
                    <h2 class="font-extrabold break-words">Aliases</h2>
                    <div class="ml-4">
                        <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark"
                           @can('create', App\Alias::class)
                           href="{{ request()->get('domain') ? route('aliases.create', ['domain' => request()->get('domain')]) : route('aliases.create') }}"
                           title="Create Alias"
                           @else
                           href="{{ route('alias-requests.create') }}"
                           title="Request Alias"
                                @endif
                        >
                            <i class="fas fa-plus mr-2" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="ml-auto">
                        @component('layout.components.search')
                            @slot('outputTextFunction', "r => r.address")
                            @slot('hiddenInputValues', $searchHiddenInputValues)
                        @endcomponent
                    </div>
                </div>
                <div class="flex flex-row flex-wrap">
                    <div class="flex flex-col">
                        @if (app('request')->input('domain') && $domain = App\Domain::whereAuthorized()->find(app('request')->input('domain')))
                            <div class="text-sm leading-normal">
                                of
                                <a class="link-black" href="{{ route('domains.show', compact('domain')) }}">
                                    {{ $domain->domain }}
                                </a>
                            </div>
                        @endif
                        @if (app('request')->input('senderMailbox') && $mailbox = App\Mailbox::whereAuthorized()->find(app('request')->input('senderMailbox')))
                            <div class="text-sm leading-normal">
                                authorized sender
                                <a class="link-black" href="{{ route('mailboxes.show', compact('mailbox')) }}">
                                    {{ $mailbox->address() }}
                                </a>
                            </div>
                        @endif
                        @if (app('request')->input('automaticallyDeactivated'))
                            <div class="text-sm leading-normal">
                                automatically deactivated
                            </div>
                        @endif
                    </div>

                    <div class="ml-auto mt-5">
                        <a class="flex flex-row items-center text-grey-darker no-underline hover:text-grey-darkest focus:text-grey-darkest"
                           href="{{ route('alias-requests.index') }}"
                           title="Show Alias Requests">
                            <div>Show Requests</div>
                            @if(isUserSuperAdmin())
                                <div class="text-sm ml-2 text-center flex flex-col items-center justify-center text-white leading-none w-6 h-6 block bg-red rounded-full">{{ App\AliasRequest::open()->count() }}</div>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
            <div class="">
                @if ($aliases->isEmpty())
                    @include('partials.empty-page')
                @endif
                @foreach($aliases as $alias)
                    <div class="border-grey-lighter border-b px-6 py-2 group2">
                        <div class="flex flex-row items-center">
                            <a class="text-grey-dark hover:text-black no-underline text-bold py-6 group pr-6 truncate"
                               title="{{ $alias->address() }}"
                               href="{{ route('aliases.show', compact('alias')) }}"
                            >
                                {{ $alias->address() }}
                                <span class="text-black opacity-0 group-hover:opacity-100">
                                    &rarr;
                                </span>
                                @if ( ! $alias->active)
                                    <div class="mx-1 tag-pill bg-red">inactive</div>
                                @endif
                            </a>

                            <div class="ml-auto hidden sm:flex flex-row items-center">
                                @if ($alias->active && $alias->deactivate_at)
                                    <div class="ml-6 flex flex-col text-center"
                                         v-tooltip="{ content: 'This alias will be deactivated<br>{{ $alias->deactivate_at->diffForHumans() }}', classes: 'text-center'}">
                                        <i class="fas fa-stopwatch text-grey p-2"
                                           aria-label="This alias will be deactivated {{ $alias->deactivate_at->diffForHumans() }}"></i>
                                    </div>
                                @endif
                                <div class="ml-6 flex flex-col text-center">
                                    <a class="text-black no-underline group"
                                       href="{{ route('mailboxes.index', ['sendingAlias' => $alias]) }}">
                                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                                            Senders
                                        </div>
                                        <div class="font-extrabold">
                                            {{ $alias->senderMailboxes()->count() }}
                                        </div>
                                    </a>
                                </div>
                                <div class="ml-6 flex flex-col text-center">
                                    <a class="text-black no-underline group"
                                       href="{{ route('mailboxes.index', ['receivingAlias' => $alias]) }}">
                                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                                            Recipients
                                        </div>
                                        <div class="font-extrabold">
                                            {{ $alias->recipientAddresses()->count() }}
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($aliases->hasPages())
                <div class="inner-p flex justify-around">
                    {{ $aliases->links() }}
                </div>
            @endif
        </div>
        <div class="w-full text-center py-3 px-2">
            @if (app('request')->input('automaticallyDeactivated'))
                <a class="link-text text-grey-darkest"
                   href="{{ route('aliases.index') }}">
                    Show normal aliases
                </a>
            @else
                <a class="link-text text-grey-darkest"
                   href="{{ route('aliases.index', ['automaticallyDeactivated' => true]) }}">
                    Show automatically deactivated aliases
                </a>
            @endif
        </div>
    </div>
@endsection