@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Aliases' => route('aliases.index'), $alias->domain->domain => route('aliases.index', ['domain' => $alias->domain]), $alias->local_part])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        @if ($alias->deactivate_at)
            <div class="py-2 mx-3">
                <mu-alert type="info" icon-class="fa-stopwatch" :show-title="false" v-cloak>
                    @if ($alias->deactivate_at->isPast() && !$alias->active)
                        This alias was automatically deactivated
                    @else
                        This alias will automatically be deactivated
                    @endif
                    {{ $alias->deactivate_at->diffForHumans() }}.
                    @if ($alias->deactivate_at->isPast() && !$alias->active)
                        Reactivate it
                        <a class="link-black"
                           href="{{ route('aliases.edit', compact('alias')) }}"
                        >here</a>.
                    @endif
                </mu-alert>
            </div>
        @endif
        <div class="dashboard-tile">
            <div class="flex flex-col md:flex-row md:items-center mb-2">
                <h2 class="font-extrabold md:mr-2 break-words">{{ $alias->address() }}</h2>
                <div class="flex flex-row flex-wrap mt-2 md:mt-0">
                    @if ( ! $alias->active)
                        <div class="mx-1 my-1 tag-pill bg-red">inactive</div>
                    @endif
                </div>
            </div>
            <p class="text-xs text-grey-dark mb-6">Created {{ $alias->created_at->diffForHumans() }} &middot;
                Updated {{ $alias->updated_at->diffForHumans() }}</p>
            <div class="mb-6">
                <p>{{ $alias->description }}</p>
            </div>
            @component('aliases.components.sendersRecipients')
                @slot('senderAndRecipientMailboxes', $senderAndRecipientMailboxes)
                @slot('senderMailboxes', $senderMailboxes)
                @slot('recipientMailboxes', $recipientMailboxes)
                @slot('externalRecipients', $externalRecipients)
            @endcomponent
            <div class="form-footer">
                @can('update', $alias)
                    <a class="btn-link" href="{{ route('aliases.edit', compact('alias')) }}">Edit</a>
                @endcan
            </div>
        </div>
    </div>
@endsection