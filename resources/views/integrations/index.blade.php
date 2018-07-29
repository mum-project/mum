@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Home' => route('home'), 'Integrations'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile-p0">
            <div class="inner-p flex flex-row items-center border-b border-grey-lighter">
                <div class="flex flex-row items-center">
                    <h2 class="font-extrabold break-words">Integrations</h2>
                    <div class="ml-4">
                        <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark" href="{{ route('integrations.create') }}" title="Create Integration">
                            <i class="fas fa-plus mr-2" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="">
                @if ($integrations->isEmpty())
                    @include('partials.empty-page')
                @endif
                @foreach($integrations as $integration)
                    <div class="border-grey-lighter border-b px-6 py-2">
                        <a class="text-grey-dark hover:text-black no-underline text-bold block py-6 group truncate"
                           title="{{ $integration->name ?: '#' . $integration->id }}"
                           href="{{ route('integrations.show', compact('integration')) }}"
                        >
                            <div class="flex flex-col md:flex-row md:items-center truncate">
                                <div class="flex flex-col truncate">
                                    <div class="uppercase tracking-wide text-xs mb-2">
                                        @if ($integration->type === App\ShellCommandIntegration::class)
                                            Shell Command
                                        @elseif ($integration->type === App\WebHookIntegration::class)
                                            Webhook
                                        @endif
                                        @if ( ! $integration->active)
                                            <div class="tag-pill bg-red text-white text-2xs ml-3 normal-case">
                                                inactive
                                            </div>
                                        @endif
                                    </div>
                                    <div class="truncate">
                                        @if ($integration->name)
                                            {{ $integration->name }}
                                        @else
                                            <code class="inline-code">
                                                @if ($integration->type === App\ShellCommandIntegration::class)
                                                    {{ config('integrations.shell_commands.' . $integration->value) }}
                                                @elseif ($integration->type === App\WebHookIntegration::class)
                                                    {{ $integration->value }}
                                                @endif
                                            </code>
                                        @endif
                                        <span class="text-black opacity-0 group-hover:opacity-100">&rarr;</span>
                                    </div>
                                </div>
                                <div class="md:ml-auto flex md:flex-row md:items-center">
                                    <div class="md:ml-6 flex flex-col md:text-center mt-2">
                                        <div class="text-grey-dark">
                                            @if ($integration->model_class === App\Domain::class)
                                                Domain
                                            @elseif ($integration->model_class === App\Mailbox::class)
                                                Mailbox
                                            @elseif ($integration->model_class === App\Alias::class)
                                                Alias
                                            @endif
                                            @
                                            {{ $integration->event_type }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            @if ($integrations->hasPages())
                <div class="inner-p flex justify-around">
                    {{ $integrations->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection