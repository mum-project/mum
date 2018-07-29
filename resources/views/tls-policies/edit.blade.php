@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['TLS Policies' => route('tls-policies.index'), $tlsPolicy->domain => route('tls-policies.show', compact('tlsPolicy')), 'Edit'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('tls-policies.update', compact('tlsPolicy')) }}" method="POST">
                @csrf
                @method('PATCH')
                <h2 class="mb-6 font-extrabold break-words">Edit TLS Policy</h2>
                <div class="form-multi-row">
                    <div class="form-group w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'domain')
                            @slot('required', true)
                            @slot('placeholder', 'example.com')
                            @slot('value', $tlsPolicy->domain)
                            The external domain that the policy should apply to.
                        @endcomponent
                    </div>
                    <div class="form-group w-1/2">
                        @component('layout.components.select')
                            @slot('name', 'policy')
                            @slot('required', true)
                            @slot('selected', $tlsPolicy->policy)
                            @slot('options', ['none', 'may' , 'encrypt', 'dane', 'dane-only', 'fingerprint', 'verify', 'secure'])
                            @slot('placeholder', '')
                            <span class="font-bold">Attention:</span> If you make a mistake here, you won't be able exchange emails with this domain.
                            For more info on the available policies, see the <a class="text-black" href="http://www.postfix.org/TLS_README.html#client_tls_policy" target="_blank" rel="nofollow noopen">Postfix Readme</a>.
                        @endcomponent
                    </div>
                </div>
                <div class="form-row">
                    @component('layout.components.input')
                        @slot('name', 'params')
                        @slot('placeholder', 'match=.example.com')
                        @slot('value', $tlsPolicy->params)
                        The parameters corresponding to the selected policy.<br>
                        <span class="font-bold">Attention:</span> If you make a mistake here, you won't be able exchange emails with this domain.
                    @endcomponent
                </div>
                <div class="form-row">
                    @component('layout.components.textarea')
                        @slot('name', 'description')
                        @slot('value', $tlsPolicy->description)
                        Maybe something to help you to remember why you added this policy.
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
        @slot('name', 'TLS Policy for ' . $tlsPolicy->domain)
        @slot('route', route('tls-policies.destroy', compact('tlsPolicy')))
    @endcomponent
@endsection