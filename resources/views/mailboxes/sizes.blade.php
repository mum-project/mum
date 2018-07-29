@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Mailboxes' => route('mailboxes.index'), $mailbox->domain->domain => route('mailboxes.index', ['domain' => $mailbox->domain]), $mailbox->local_part => route('mailboxes.show', compact('mailbox')), 'Disk Usage'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p flex flex-col border-grey-lighter border-b">
                <div class="flex flex-row items-center">
                    <h2 class="font-extrabold break-words">Disk Usage</h2>
                    @if (!$sizeMeasurements->isEmpty() && isUserSuperAdmin())
                        <div class="ml-auto">
                            <button class="btn-link" @click.prevent="showPopupModal = true">
                                Clear history
                            </button>
                        </div>
                    @endif
                </div>
                <div class="text-sm leading-normal">
                    by
                    <a class="link-black" href="{{ route('mailboxes.show', compact('mailbox')) }}">
                        {{ $mailbox->address() }}
                    </a>
                </div>
            </div>
            <div class="">
                @if ($sizeMeasurements->isEmpty())
                    @include('partials.empty-page')
                @else
                    <size-measurements-chart
                            :labels="{{ $sizeMeasurements->pluck('created_at')->map(function($ts) { return (string)$ts; }) }}"
                            :values="{{ $sizeMeasurements->pluck('size') }}"
                    ></size-measurements-chart>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @component('layout.components.deleteModal')
        @slot('name', 'all disk usage measurements for ' . $mailbox->address())
        @slot('route', route('mailboxes.sizes', compact('mailbox')))
        @slot('noNameFormatting', true)
    @endcomponent
@endsection