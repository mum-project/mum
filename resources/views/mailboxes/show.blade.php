@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Mailboxes' => route('mailboxes.index'), $mailbox->domain->domain => route('mailboxes.index', ['domain' => $mailbox->domain]), $mailbox->local_part])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <div class="flex flex-col md:flex-row md:items-center mb-2">
                <h2 class="font-extrabold md:mr-2 break-words">{{ $mailbox->address() }}</h2>
                <div class="flex flex-row flex-wrap mt-2 md:mt-0">
                    @if ( ! $mailbox->active)
                        <div class="mx-1 my-1 tag-pill bg-red">inactive</div>
                    @endif
                    @if ($mailbox->isSuperAdmin())
                        <div class="mx-1 my-1 tag-pill bg-blue">Super Admin</div>
                    @endif
                    @if ($mailbox->send_only)
                        <div class="mx-1 my-1 tag-pill bg-orange-dark">Send Only</div>
                    @endif
                </div>
            </div>
            <p class="text-xs text-grey-dark mb-8">Created {{ $mailbox->created_at->diffForHumans() }} &middot;
                Updated {{ $mailbox->updated_at->diffForHumans() }}</p>

            <div class="flex flex-col mb-8">
                <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                    Name
                </div>
                <div class="">
                    {!! $mailbox->name ? htmlspecialchars($mailbox->name) : '&mdash;' !!}
                </div>
            </div>

            <div class="flex flex-col mb-8">
                <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                    Quota
                </div>
                <div class="">
                    {!! $mailbox->quota ? htmlspecialchars($mailbox->quota) . ' GB' : '&mdash;' !!}
                </div>
            </div>

            <div class="flex flex-col mb-8">
                <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                    Alternative E-Mail
                </div>
                <div class="">
                    {!! $mailbox->alternative_email ? htmlspecialchars($mailbox->alternative_email) : '&mdash;' !!}
                </div>
            </div>

            @if (isUserSuperAdmin())
                <div class="flex flex-col mb-8">
                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                        Home Directory
                    </div>
                    <div class="font-mono text-sm">
                        {{ $mailbox->homedir }}
                    </div>
                </div>
            @endif

            @if (isUserSuperAdmin())
                <div class="flex flex-col mb-8">
                    <div class="text-xs uppercase tracking-wide mb-2 text-grey-dark">
                        Mail Directory
                    </div>
                    <div class="font-mono text-sm">
                        {{ $mailbox->maildir }}
                    </div>
                </div>
            @endif

            <div class="form-footer">
                @can ('update', $mailbox)
                    <a class="btn-link" href="{{ route('mailboxes.edit', compact('mailbox')) }}">Edit</a>
                @endcan
                @if ($mailbox->sizeMeasurements()->exists())
                <a class="btn-link" href="{{ route('mailboxes.sizes', compact('mailbox')) }}">Disk Usage</a>
                @endif
            </div>
        </div>
    </div>
@endsection