@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['System Services'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p flex flex-col border-grey-lighter border-b">
                <div class="flex flex-row items-center">
                    <h2 class="font-extrabold break-words">System Services</h2>
                    <div class="ml-4">
                        <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark"
                           href="{{ route('system-services.create') }}"
                           title="Create System Service">
                            <i class="fas fa-plus mr-2" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="ml-auto">
                        @component('layout.components.search')
                            @slot('outputTextFunction', "r => r.name")
                            @slot('hiddenInputValues', $searchHiddenInputValues)
                        @endcomponent
                    </div>
                </div>
            </div>
            <div class="">
                @if ($systemServices->isEmpty())
                    @include('partials.empty-page')
                @endif
                @foreach($systemServices as $systemService)
                    <div class="border-grey-lighter border-b px-6 py-2">
                        <div class="flex flex-row items-center">
                            <a class="text-grey-dark hover:text-black no-underline text-bold py-6 group pr-6 truncate"
                               title="{{ $systemService->name() }}"
                               href="{{ route('system-services.show', compact('systemService')) }}"
                            >
                                {{ $systemService->name() }}
                                <span class="text-black opacity-0 group-hover:opacity-100">
                                    &rarr;
                                </span>
                            </a>
                            <div class="ml-auto hidden sm:flex flex-row items-center">
                                <div class="ml-6 flex flex-col text-center">
                                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                                        Last Status
                                    </div>
                                    <div class="mx-auto text-center">
                                        @if ($systemService->latestServiceHealthCheck())
                                            <div class="tag-pill text-white @if($systemService->latestServiceHealthCheck()->wasRunning()) bg-green @else bg-red @endif">{{ $systemService->latestServiceHealthCheck()->output }}</div>
                                        @else
                                            <div class="font-extrabold">?</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($systemServices->hasPages())
                <div class="inner-p flex justify-around">
                    {{ $systemServices->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection