@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Home' => route('home'), 'TLS Policies'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p flex flex-row items-center border-b border-grey-lighter">
                <h2 class="font-extrabold break-words">TLS Policies</h2>
                <div class="ml-4">
                    <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark" href="{{ route('tls-policies.create') }}" title="Create TLS Policy">
                        <i class="fas fa-plus mr-2" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
            <div class="">
                @if ($tlsPolicies->isEmpty())
                    @include('partials.empty-page')
                @endif
                @foreach($tlsPolicies as $tlsPolicy)
                    <div class="border-grey-lighter border-b px-6 py-2">
                        <div class="flex flex-row items-center">
                            <a class="text-grey-dark hover:text-black no-underline text-bold py-6 group pr-6 truncate"
                               title="{{ $tlsPolicy->domain }}"
                               href="{{ route('tls-policies.show', compact('tlsPolicy')) }}"
                            >
                                {{ $tlsPolicy->domain }}
                                <span class="text-black opacity-0 group-hover:opacity-100">
                                    &rarr;
                                </span>
                            </a>
                            @if ( ! $tlsPolicy->active)
                                <div class="-ml-3 tag-pill {{ $tlsPolicy->active ? 'bg-green' : 'bg-red' }}">
                                    {{ $tlsPolicy->active ? 'active' : 'inactive' }}
                                </div>
                            @endif
                            <div class="ml-auto hidden sm:flex flex-row items-center">
                                <div class="ml-6 flex flex-col text-left">
                                    @if ($tlsPolicy->params)
                                        <v-popover trigger="hover">
                                            <div class="font-extrabold py-1 py-1">
                                                {{ $tlsPolicy->policy }}
                                            </div>
                                            <template slot="popover">
                                                <p class="max-w-xs overflow-hidden break-words">{{ $tlsPolicy->params }}</p>
                                            </template>
                                        </v-popover>
                                    @else
                                        <div class="font-extrabold py-1 py-1">
                                            {{ $tlsPolicy->policy }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($tlsPolicies->hasPages())
                <div class="inner-p flex justify-around">
                    {{ $tlsPolicies->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection