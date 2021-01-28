@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['TLS Policies' => route('tls-policies.index'), $tlsPolicy->domain])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <div class="flex flex-col md:flex-row md:items-center mb-2">
                <h2 class="font-extrabold md:mr-2 break-words">TLS Policy for {{ $tlsPolicy->domain }}</h2>
                <div class="flex flex-row flex-wrap mt-2 md:mt-0">
                    @if ( ! $tlsPolicy->active)
                        <div class="mx-1 my-1 tag-pill bg-red">inactive</div>
                    @endif
                </div>
            </div>
            <p class="text-xs text-grey-dark mb-8">Created {{ $tlsPolicy->created_at->diffForHumans() }} &middot;
                Updated {{ $tlsPolicy->updated_at->diffForHumans() }}</p>
            <div class="flex flex-col md:flex-row flex-wrap justify-around max-w-full">
                <div class="flex flex-col text-center px-3 mb-8">
                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                        Policy
                    </div>
                    <div class="font-extrabold text-lg">
                        {{ $tlsPolicy->policy }}
                    </div>
                </div>
                <div class="flex flex-col text-center px-3 mb-8 max-w-full">
                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                        Parameters
                    </div>
                    <div class="font-extrabold font-mono text-lg max-w-full break-words">
                        {!! $tlsPolicy->params ? htmlspecialchars($tlsPolicy->params) : '&#151;' !!}
                    </div>
                </div>
            </div>
            @if ($tlsPolicy->description)
                <p class="mb-6">{{ $tlsPolicy->description }}</p>
            @endif
            <div class="form-footer">
                <a class="btn-link" href="{{ route('tls-policies.edit', ['tls_policy' => $tlsPolicy]) }}">Edit</a>
            </div>
        </div>
    </div>
@endsection