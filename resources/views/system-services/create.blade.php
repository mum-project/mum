@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['System Services' => route('system-services.index'), 'Add'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('system-services.store') }}" method="POST">
                @csrf
                <h2 class="mb-6 font-extrabold break-words">Add a System Service</h2>
                <div class="form-row">
                    <p class="leading-normal">
                        To automatically monitor the running state of a system service, please enter it's name below.<br>
                    </p>
                </div>
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'service')
                            @slot('placeholder', 'mysql')
                            @slot('required', true)
                            The system service that should be monitored.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'name')
                            @slot('label', 'Pretty Name')
                            @slot('placeholder', 'MySQL')
                            You may specify a prettier name for the service that will be shown in the web interface.
                        @endcomponent
                    </div>
                </div>
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Start Monitoring</button>
                    <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection