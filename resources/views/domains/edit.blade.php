@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Domains' => route('domains.index'), $domain->domain => route('domains.show', compact('domain')), 'Edit'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('domains.update', compact('domain')) }}" method="POST">
                @csrf
                @method('PATCH')
                <h2 class="mb-6 font-extrabold break-words">Update {{ $domain->domain }}</h2>
                <div class="form-row max-w-sm">
                    @component('layout.components.input')
                        @slot('name', 'domain')
                        @slot('placeholder', 'example.com')
                        @slot('value', $domain->domain)
                        Please specify a valid domain.
                    @endcomponent
                </div>
                <div class="form-row">
                    @component('layout.components.textarea')
                        @slot('name', 'description')
                        @slot('value', $domain->description)
                        Maybe something to help you to remember what this domain is used for.
                    @endcomponent
                </div>
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'max_mailboxes')
                            @slot('label', 'Maximum Mailboxes')
                            @slot('type', 'number')
                            @slot('extraProps', 'step="1" min="0"')
                            @slot('value', $domain->max_mailboxes)
                            How many mailboxes should be available for this domain?<br/>Leave blank for
                            no limit.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'max_aliases')
                            @slot('label', 'Maximum Aliases')
                            @slot('type', 'number')
                            @slot('extraProps', 'step="1" min="0"')
                            @slot('value', $domain->max_aliases)
                            How many aliases should be available for this domain?<br/>Leave blank for
                            no limit.
                        @endcomponent
                    </div>
                </div>
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'quota')
                            @slot('type', 'number')
                            @slot('extraProps', 'min="0"')
                            @slot('addon', 'GB')
                            @slot('value', $domain->quota)
                            Maximum storage space a mailbox may use. Leave blank for no limit. This
                            value can be overwritten by a mailbox setting.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'max_quota')
                            @slot('label', 'Maximum Quota')
                            @slot('type', 'number')
                            @slot('extraProps', 'min="0"')
                            @slot('addon', 'GB')
                            @slot('value', $domain->max_quota)
                            Maximum storage space that can be assigned to the mailbox's quota setting.
                            Leave blank for no limit.
                        @endcomponent
                    </div>
                </div>
                <div class="form-row">
                    @component('layout.components.checkbox')
                        @slot('name', 'active')
                        @slot('label', 'Active')
                        @slot('checked', $domain->active)
                        Attention: if disabled, all mailboxes and aliases of this domain will not be able to login or receive emails.
                    @endcomponent
                </div>
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                    <a class="btn-link ml-auto hover:text-red" href="#" @click.prevent="showPopupModal = true">Delete</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('modal')
    @component('layout.components.deleteModal')
        @slot('name', $domain->domain)
        @slot('route', route('domains.destroy', compact('domain')))
    @endcomponent
@endsection
