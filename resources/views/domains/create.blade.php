@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Domains' => route('domains.index'), 'Create'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('domains.store') }}" method="POST">
                @csrf
                <h2 class="mb-6 font-extrabold break-words">Create Domain</h2>
                <div class="form-row max-w-sm">
                    @component('layout.components.input')
                        @slot('name', 'domain')
                        @slot('placeholder', 'example.com')
                        @slot('required', true)
                        Please specify a valid domain.
                    @endcomponent
                </div>
                <div class="form-row">
                    @component('layout.components.textarea')
                        @slot('name', 'description')
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
                            @slot('value', config('mum.domains.quota'))
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
                            @slot('value', config('mum.domains.max_quota'))
                            Maximum storage space that can be assigned to the mailbox's quota setting.
                            Leave blank for no limit.
                        @endcomponent
                    </div>
                </div>
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Create</button>
                    <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection