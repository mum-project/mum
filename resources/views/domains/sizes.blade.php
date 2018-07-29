@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Domains' => route('domains.index'), $domain->domain => route('domains.show', compact('domain')), 'Disk Usage'])
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
                    <a class="link-black" href="{{ route('domains.show', compact('domain')) }}">
                        {{ $domain->domain }}
                    </a>
                </div>
            </div>
            <div class="">
                @if ($sizeMeasurements->isEmpty())
                    @include('partials.empty-page').
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
        @slot('name', 'all disk usage measurements for ' . $domain->domain)
        @slot('route', route('domains.sizes', compact('domain')))
        @slot('noNameFormatting', true)
    @endcomponent
@endsection