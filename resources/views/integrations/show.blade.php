@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Home' => route('home'), 'Integrations' => route('integrations.index'), '#' . $integration->id])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <div class="flex flex-col md:flex-row md:items-center mb-2">
                <h2 class="font-extrabold md:mr-2 break-words">
                    @if ($integration->name)
                        {{ $integration->name }}
                    @else
                        Integration #{{ $integration->id }}
                    @endif
                </h2>
                <div class="flex flex-row flex-wrap mt-2 md:mt-0">
                    @if ( ! $integration->active)
                        <div class="mx-1 my-1 tag-pill bg-red">inactive</div>
                    @endif
                </div>
            </div>
            <p class="text-xs text-grey-dark mb-8">Created {{ $integration->created_at->diffForHumans() }} &middot;
                Updated {{ $integration->updated_at->diffForHumans() }}</p>
            <div class="mb-8">
                <p class="mb-8">
                    This integration triggers a
                    @if ($integration->type === App\ShellCommandIntegration::class)
                        shell command
                    @elseif ($integration->type === App\WebHookIntegration::class)
                        webhook
                    @endif
                    every time
                    @if ($integration->model_class === App\Domain::class)
                        a domain
                    @elseif ($integration->model_class === App\Mailbox::class)
                        a mailbox
                    @elseif ($integration->model_class === App\Alias::class)
                        an alias
                    @endif
                    is
                    {{ $integration->event_type }}.
                </p>
                <h3 class="mb-3">
                    @if ($integration->type === App\ShellCommandIntegration::class)
                        Command
                    @elseif ($integration->type === App\WebHookIntegration::class)
                        URL
                    @endif
                </h3>
                <code class="inline-code">
                    @if ($integration->type === App\ShellCommandIntegration::class)
                        {{ config('integrations.shell_commands.' . $integration->value) }}
                    @elseif ($integration->type === App\WebHookIntegration::class)
                        {{ $integration->value }}
                    @endif
                </code>
                @if ($integration->parameters()->exists())
                    <h3 class="mt-6 mb-2">
                        Parameters
                    </h3>
                    <div class="-mx-1">
                        @foreach($integration->parameters as $parameter)
                            <code class="inline-code my-1 mx-1">{{ $parameter->getParameterString() }}</code>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="form-footer">
                <a class="btn-link" href="{{ route('integrations.edit', compact('integration')) }}">Edit</a>
            </div>
        </div>
    </div>
@endsection