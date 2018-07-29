@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @if (app('request')->input('domain') && $domain = App\Domain::find(app('request')->input('domain')))
            @slot('links', ['Mailboxes' => route('mailboxes.index'), $domain->domain])
        @else
            @slot('links', ['Mailboxes'])
        @endif
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p flex flex-col border-grey-lighter border-b">
                <div class="flex flex-row items-center">
                    <h2 class="font-extrabold break-words">Mailboxes</h2>
                    <div class="ml-4">
                        <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark"
                           href="{{ request()->get('domain') ? route('mailboxes.create', ['domain' => request()->get('domain')]) : route('mailboxes.create') }}"
                           title="Create Mailbox">
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
                @if (app('request')->input('domain') && $domain = App\Domain::whereAuthorized()->find(app('request')->input('domain')))
                    <div class="text-sm leading-normal">
                        of
                        <a class="link-black" href="{{ route('domains.show', compact('domain')) }}">
                            {{ $domain->domain }}
                        </a>
                    </div>
                @endif
                @if (app('request')->input('administratedDomain') && $domain = App\Domain::whereAuthorized()->find(app('request')->input('administratedDomain')))
                    <div class="text-sm leading-normal">
                        administrating
                        <a class="link-black" href="{{ route('domains.show', compact('domain')) }}">
                            {{ $domain->domain }}
                        </a>
                    </div>
                @endif
                @if (app('request')->input('sendingAlias') && $alias = App\Alias::whereAuthorized()->find(app('request')->input('sendingAlias')))
                    <div class="text-sm leading-normal">
                        sending from
                        <a class="link-black" href="{{ route('aliases.show', compact('alias')) }}">
                            {{ $alias->address() }}
                        </a>
                    </div>
                @endif
                @if (app('request')->input('receivingAlias') && $alias = App\Alias::whereAuthorized()->find(app('request')->input('receivingAlias')))
                    <div class="text-sm leading-normal">
                        receiving from
                        <a class="link-black" href="{{ route('aliases.show', compact('alias')) }}">
                            {{ $alias->address() }}
                        </a>
                    </div>
                @endif
            </div>
            <div class="">
                @if ($mailboxes->isEmpty())
                    @include('partials.empty-page')
                @endif
                @foreach($mailboxes as $mailbox)
                    <div class="border-grey-lighter border-b px-6 py-2">
                        <div class="flex flex-row items-center">
                            <a class="text-grey-dark hover:text-black no-underline text-bold py-6 group pr-6 truncate"
                               title="{{ $mailbox->address() }}"
                               href="{{ route('mailboxes.show', compact('mailbox')) }}"
                            >
                                {{ $mailbox->address() }}
                                <span class="text-black opacity-0 group-hover:opacity-100 select-none">
                                        &rarr;
                                    </span>
                                @if ( ! $mailbox->active)
                                    <div class="mx-1 tag-pill bg-red">inactive</div>
                                @endif
                            </a>
                            <div class="ml-auto hidden sm:flex flex-row items-center  select-none">
                                <div class="ml-6 flex flex-col text-center">
                                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                                        Quota
                                    </div>
                                    <div class="font-extrabold">
                                        {!! $mailbox->quota ? htmlspecialchars($mailbox->quota) . ' GB' : '&infin;' !!}
                                    </div>
                                </div>
                                <div class="ml-6 flex flex-col text-center">
                                    <a class="text-black no-underline group"
                                       href="{{ route('aliases.index', ['senderMailbox' => $mailbox]) }}">
                                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                                            Aliases
                                        </div>
                                        <div class="font-extrabold">
                                            {{ $mailbox->sendingAliases()->count() }}
                                        </div>
                                    </a>
                                </div>
                                <div class="ml-6 flex flex-col text-center">
                                    <a class="text-black no-underline group"
                                       href="{{ route('domains.index', ['admin' => $mailbox]) }}">
                                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                                            Domains
                                        </div>
                                        <div class="font-extrabold">
                                            {{ $mailbox->administratedDomains()->count() }}
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($mailboxes->hasPages())
                <div class="inner-p flex justify-around">
                    {{ $mailboxes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection