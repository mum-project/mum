@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['System Services' => route('system-services.index'), $systemService->name()])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <div class="flex flex-col md:flex-row md:items-center mb-2">
                <h2 class="font-extrabold md:mr-2 break-words">{{ $systemService->name() }} Service</h2>
                <div class="flex flex-row flex-wrap mt-2 md:mt-0"></div>
            </div>
            <p class="text-xs text-grey-dark mb-8">Created {{ $systemService->created_at->diffForHumans() }} &middot;
                Updated {{ $systemService->updated_at->diffForHumans() }}</p>
            <div class="flex flex-col mb-8">
                <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                    Service Name
                </div>
                <div class="font-mono">
                    {{ $systemService->service }}
                </div>
            </div>
            <div class="flex flex-col mb-8">
                <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                    Last Check
                </div>
                <div class="mt-1 flex flex-row items-baseline">
                    @if ($systemService->latestServiceHealthCheck())
                        @if ($systemService->latestServiceHealthCheck()->wasRunning())
                            <div class="w-3 h-3 rounded-full bg-green ml-1"></div>
                        @else
                            <div class="w-3 h-3 rounded-full bg-red ml-1"></div>
                        @endif
                        <div class="ml-3 flex flex-col">
                            <div>
                                Status:
                                <code class="inline-code">{{ $systemService->latestServiceHealthCheck()->output }}</code>
                            </div>
                            <div class="mt-1 text-xs text-grey-dark">
                                {{ $systemService->latestServiceHealthCheck()->created_at }}
                            </div>
                        </div>
                    @else
                        <p class="-mt-1 text-grey-dark italic">This service was not checked yet.</p>
                    @endif
                </div>
            </div>
            @if ($systemService->latestServiceHealthCheck())
                <div class="flex flex-col mb-3">
                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                        Incident History
                    </div>
                    @if ($incidentHistory->isEmpty())
                        <p class="text-grey-dark italic">Yay, there are no incident records for this service!</p>
                    @endif
                    <div class="mt-1 flex flex-row flex-wrap justify-between -mx-3">
                        @foreach($incidentHistory as $healthCheck)
                            <div class="flex flex-row items-baseline mx-4 mb-5">
                                <div class="rounded-full h-3 w-3 bg-red-light"></div>
                                <div class="ml-3 flex flex-col">
                                    <div>
                                        Status: <code class="inline-code">{{ $healthCheck->output }}</code>
                                    </div>
                                    <div class="mt-1 text-xs text-grey-dark">
                                        {{ $healthCheck->created_at }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="form-footer">
                @can ('update', $systemService)
                    <a class="btn-link" href="{{ route('system-services.edit', compact('systemService')) }}">Edit</a>
                @endcan
            </div>
        </div>
    </div>
@endsection