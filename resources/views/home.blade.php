@extends('layout.master')

@section('content')
    <div class="max-w-full w-full flex flex-col lg:flex-row">
        <div class="lg:w-1/2">
            <div class="flex flex-col">
                <div class="flex flex-row flex-wrap">
                    <div class="dashboard-tile leading-normal">
                        <p class="font-extrabold text-2xl">
                            @if (Carbon\Carbon::now()->hour > 18 && Carbon\Carbon::now()->hour)
                                Good evening,
                            @elseif (Carbon\Carbon::now()->hour > 12)
                                Good afternoon,
                            @else
                                Good morning,
                            @endif
                            {{ Auth::user()->name ?: Auth::user()->address() }}!
                        </p>
                        @if (Auth::user()->isAdmin())
                            <p class="mt-8 mb-3 text-grey-dark">
                                You want to create something quickly? These links might help you:
                            </p>
                            <div class="flex flex-row flex-wrap -mx-4 -my-1 justify-between">
                                @can('create', App\Domain::class)
                                    <a href="{{ route('domains.create') }}" class="link-black px-4 py-1 my-1">
                                        Create Domain
                                    </a>
                                @endcan
                                @can('create', App\Mailbox::class)
                                    <a href="{{ route('mailboxes.create') }}" class="link-black px-4 py-1 my-1">
                                        Create Mailbox
                                    </a>
                                @endcan
                                @can('create', App\Alias::class)
                                    <a href="{{ route('aliases.create') }}" class="link-black px-4 py-1 my-1">
                                        Create Alias
                                    </a>
                                @endcan
                                @can('create', App\AliasRequest::class)
                                    <a href="{{ route('alias-requests.create') }}" class="link-black px-4 py-1 my-1">
                                        Request Alias
                                    </a>
                                @endcan
                            </div>
                        @else

                        @endif
                    </div>
                </div>
                <div class="flex flex-row flex-wrap">
                    <div class="dashboard-tile flex flex-col items-center">
                        <a href="{{ route('domains.index') }}" class="text-black text-center no-underline group">
                            <div class="text-5xl font-light">{{ App\Domain::whereAuthorized()->count() }}</div>
                            <small class="uppercase text-grey-dark tracking-wide group-hover:text-black">Domains</small>
                        </a>
                    </div>
                    <div class="dashboard-tile flex flex-col items-center">
                        <a href="{{ route('mailboxes.index') }}" class="text-black text-center no-underline group">
                            <div class="text-5xl font-light">{{ App\Mailbox::whereAuthorized()->count() }}</div>
                            <small class="uppercase text-grey-dark tracking-wide group-hover:text-black">Mailboxes
                            </small>
                        </a>
                    </div>
                    <div class="dashboard-tile flex flex-col items-center">
                        <a href="{{ route('aliases.index') }}" class="text-black text-center no-underline group">
                            <div class="text-5xl font-light">{{ App\Alias::whereAuthorized()->count() }}</div>
                            <small class="uppercase text-grey-dark tracking-wide group-hover:text-black">Aliases</small>
                        </a>
                    </div>
                </div>
                @if (config('mum.email_settings.show'))
                    <div class="flex flex-row flex-wrap">
                        <div class="dashboard-tile flex flex-col">
                            <h3 class="font-bold mb-3">Email Settings</h3>
                            <p class="text-grey-dark leading-normal">
                                If you want to add your account to an email client like Mozilla Thunderbird,<br>
                                you should use the following settings:
                            </p>
                            <div class="pt-4 flex flex-col flex-wrap sm:flex-row -m-2">
                                <div class="flex flex-col flex-grow mx-2 py-6">
                                    <h4 class="mb-3">SMTP</h4>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            Hostname
                                        </div>
                                        <div class="">{{ config('mum.email_settings.smtp.hostname') }}</div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            Port
                                        </div>
                                        <div class="">{{ config('mum.email_settings.smtp.port') }}</div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            SSL
                                        </div>
                                        <div class="">{{ config('mum.email_settings.smtp.ssl') }}</div>
                                    </div>
                                    <div class="">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            User
                                        </div>
                                        <div class="">{{ Auth::user()->address() }}</div>
                                    </div>
                                </div>
                                <div class="flex flex-col flex-grow mx-2 py-6">
                                    <h4 class="mb-3">IMAP</h4>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            Hostname
                                        </div>
                                        <div class="">{{ config('mum.email_settings.imap.hostname') }}</div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            Port
                                        </div>
                                        <div class="">{{ config('mum.email_settings.imap.port') }}</div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            SSL
                                        </div>
                                        <div class="">{{ config('mum.email_settings.imap.ssl') }}</div>
                                    </div>
                                    <div class="">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            User
                                        </div>
                                        <div class="">{{ Auth::user()->address() }}</div>
                                    </div>
                                </div>
                                <div class="flex flex-col flex-grow mx-2 py-6">
                                    <h4 class="mb-3">POP3</h4>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            Hostname
                                        </div>
                                        <div class="">{{ config('mum.email_settings.pop3.hostname') }}</div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            Port
                                        </div>
                                        <div class="">{{ config('mum.email_settings.pop3.port') }}</div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            SSL
                                        </div>
                                        <div class="">{{ config('mum.email_settings.pop3.ssl') }}</div>
                                    </div>
                                    <div class="">
                                        <div class="uppercase tracking-wide text-grey-dark text-xs font-bold mb-1">
                                            User
                                        </div>
                                        <div class="">{{ Auth::user()->address() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="lg:w-1/2">
            <div class="dashboard-tile-p0">
                <h3 class="text-center font-bold px-6 pt-6">Disk Usage of all Domains</h3>
                <div class="block relative">
                    @if ($rootFolderSizeMeasurements->isEmpty())
                        @include('partials.empty-page')
                    @else
                        <size-measurements-chart
                                :labels="{{ $rootFolderSizeMeasurements->pluck('created_at')->map(function($ts) { return (string)$ts; }) }}"
                                :values="{{ $rootFolderSizeMeasurements->pluck('size') }}"
                                :show-grid-lines="{ x: false, y: false }"
                                :padding="{ t: 10, r: 10, b: 10, l: 10 }"
                                :show-points="false"
                        ></size-measurements-chart>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

