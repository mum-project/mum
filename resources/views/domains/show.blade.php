@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Domains' => route('domains.index'), $domain->domain])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <div class="flex flex-col md:flex-row md:items-center mb-2">
                <h2 class="font-extrabold md:mr-2 break-words">{{ $domain->domain }}</h2>
                <div class="flex flex-row flex-wrap md:mt-0">
                    @if ( ! $domain->active)
                        <div class="mx-1 my-1 tag-pill bg-red">inactive</div>
                    @endif
                </div>
            </div>
            <p class="text-xs text-grey-dark mb-8">Created {{ $domain->created_at->diffForHumans() }} &middot;
                Updated {{ $domain->updated_at->diffForHumans() }}</p>
            <div class="flex flex-row justify-around mb-8">
                <div class="flex flex-col text-center px-3">
                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                        Quota
                    </div>
                    <div class="font-extrabold text-lg">
                        {!! $domain->quota ? htmlspecialchars($domain->quota . ' GB') : '&infin;' !!}
                    </div>
                </div>
                <div class="flex flex-col text-center px-3">
                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                        Max Quota
                    </div>
                    <div class="font-extrabold text-lg">
                        {!! $domain->max_quota ? htmlspecialchars($domain->max_quota . ' GB') : '&infin;' !!}
                    </div>
                </div>
                <div class="flex flex-col text-center px-3">
                    <a class="text-black no-underline group"
                       href="{{ route('mailboxes.index', ['administratedDomain' => $domain]) }}">
                        <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark group-hover:text-black">
                            Admins
                        </div>
                        <div class="font-extrabold text-lg">
                            {{ $domain->admins->count() }}
                        </div>
                    </a>
                </div>
                <div class="flex flex-col text-center px-3">
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
                <div class="flex flex-col text-center px-3">
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
            @if ($domain->description)
                <p class="mb-6">{{ $domain->description }}</p>
            @endif
            <div class="form-footer">
                @can('update', $domain)
                    <a class="btn-link" href="{{ route('domains.edit', compact('domain')) }}">Edit</a>
                @endcan
                @if ($domain->sizeMeasurements()->exists())
                    <a class="btn-link" href="{{ route('domains.sizes', compact('domain')) }}">Disk Usage</a>
                @endif
            </div>
        </div>
    </div>
@endsection