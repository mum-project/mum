@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Domains'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p flex flex-col border-grey-lighter border-b">
                <div class="flex flex-row items-center">
                    <div class="flex flex-row items-center">
                        <h2 class="font-extrabold break-words">Domains</h2>
                        <div class="ml-4">
                            <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark" href="{{ route('domains.create') }}"
                               title="Create Domain">
                                <i class="fas fa-plus mr-2" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ml-auto">
                        @component('layout.components.search')
                            @slot('outputTextFunction', "r => r.domain")
                            @slot('hiddenInputValues', $searchHiddenInputValues)
                        @endcomponent
                    </div>
                </div>
                @if (app('request')->input('admin') && $mailbox = App\Mailbox::whereAuthorized()->find(app('request')->input('admin')))
                    <div class="text-sm leading-normal">
                        administrated by
                        <a class="link-black" href="{{ route('mailboxes.show', compact('mailbox')) }}">
                            {{ $mailbox->address() }}
                        </a>
                    </div>
                @endif
            </div>
            <div class="">
                @if ($domains->isEmpty())
                    @include('partials.empty-page')
                @endif
                @foreach($domains as $domain)
                    <div class="border-grey-lighter border-b px-6 py-2 group2">
                        <div class="flex flex-row items-center">
                            <a class="text-grey-dark hover:text-black no-underline text-bold py-6 group pr-6 truncate"
                               title="{{ $domain->domain }}"
                               href="{{ route('domains.show', compact('domain')) }}"
                            >
                                {{ $domain->domain }}
                                <span class="text-black opacity-0 group-hover:opacity-100">
                                    &rarr;
                                </span>
                                @if ( ! $domain->active)
                                    <div class="mx-1 tag-pill bg-red">inactive</div>
                                @endif
                            </a>
                            <div class="ml-auto hidden sm:flex flex-row items-center">
                                <div class="ml-6 flex flex-col text-center">
                                    <a class="text-black no-underline group"
                                       href="{{ route('mailboxes.index', ['administratedDomain' => $domain]) }}">
                                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                                            Admins
                                        </div>
                                        <div class="font-extrabold">
                                            {{ $domain->admins->count() }}
                                        </div>
                                    </a>
                                </div>
                                <div class="ml-6 flex flex-col text-center">
                                    <a class="text-black no-underline group"
                                       href="{{ route('mailboxes.index', ['domain' => $domain]) }}">
                                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                                            Mailboxes
                                        </div>
                                        <div class="font-extrabold{{ $domain->isMailboxContingentShort() ? ' text-red' : '' }}">
                                            {{ $domain->mailboxes->count() }} <span
                                                    class="font-normal">/</span> {!! $domain->max_mailboxes ? htmlspecialchars($domain->max_mailboxes) : '&infin;' !!}
                                        </div>
                                    </a>
                                </div>
                                <div class="ml-6 flex flex-col text-center">
                                    <a class="text-black no-underline group"
                                       href="{{ route('aliases.index', ['domain' => $domain]) }}">
                                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                                            Aliases
                                        </div>
                                        <div class="font-extrabold{{ $domain->isAliasContingentShort() ? ' text-red' : '' }}">
                                            {{ $domain->aliases->count() }} <span
                                                    class="font-normal">/</span> {!! $domain->max_aliases ? htmlspecialchars($domain->max_aliases) : '&infin;' !!}
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($domains->hasPages())
                <div class="inner-p flex justify-around">
                    {{ $domains->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection