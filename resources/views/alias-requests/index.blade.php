@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @if (app('request')->input('domain') && $domain = App\Domain::find(app('request')->input('domain')))
            @slot('links', ['Aliases' => route('aliases.index'), 'Requests' => route('alias-requests.index'), $domain->domain])
        @else
            @slot('links', ['Aliases' => route('aliases.index'), 'Requests'])
        @endif
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p flex flex-col">
                <div class="flex flex-row items-center">
                    <h2 class="font-extrabold break-words">Alias Requests</h2>
                    <div class="ml-4">
                        @can('create', App\AliasRequest::class)
                            <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark"
                               href="{{ request()->get('domain') ? route('alias-requests.create', ['domain' => request()->get('domain')]) : route('alias-requests.create') }}"
                               title="Create Alias Request">
                                <i class="fas fa-plus mr-2" aria-hidden="true"></i>
                            </a>
                        @endcan
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
            </div>
            <div class="">
                <div class="tab-bar -mt-2 mb-2 mx-5">
                    <a href="{{ route('alias-requests.index', ['status' => 'open']) }}"
                       class="tab-title {{ app('request')->input('status') === 'open' || !app('request')->input('status') ? 'active' : '' }}"
                    >open</a>
                    <a href="{{ route('alias-requests.index', ['status' => 'approved']) }}"
                       class="tab-title {{ app('request')->input('status') === 'approved' ? 'bg-green text-white font-bold' : '' }}"
                    >approved</a>
                    <a href="{{ route('alias-requests.index', ['status' => 'dismissed']) }}"
                       class="tab-title {{ app('request')->input('status') === 'dismissed' ? 'bg-red text-white font-bold' : '' }}"
                    >dismissed</a>
                </div>
                @if ($aliasRequests->isEmpty())
                    @include('partials.empty-page')
                @endif
                @foreach($aliasRequests as $aliasRequest)
                    <div class="border-grey-lighter border-b px-6 py-2 group2">
                        <div class="flex flex-row items-center">
                            <a class="text-grey-dark hover:text-black no-underline text-bold py-6 group pr-6 truncate"
                               title="{{ $aliasRequest->address() }}"
                               href="{{ route('alias-requests.show', compact('aliasRequest')) }}"
                            >
                                {{ $aliasRequest->address() }}
                                <span class="text-black opacity-0 group-hover:opacity-100">
                                    &rarr;
                                </span>
                            </a>
                            <div class="ml-auto hidden sm:flex flex-row items-center">
                                <div class="ml-6 flex flex-col text-right">
                                    <a class="text-black no-underline focus:underline group"
                                       href="{{ route('mailboxes.show', ['mailbox' => $aliasRequest->mailbox]) }}">
                                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                                            Requested by
                                        </div>
                                        <div class="">
                                            {{ $aliasRequest->mailbox->address() }}
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($aliasRequests->hasPages())
                <div class="inner-p flex justify-around">
                    {{ $aliasRequests->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection