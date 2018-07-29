@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['System Services' => route('system-services.index'), $systemService->name() => route('system-services.show', compact('systemService')), 'Edit'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <div class="dashboard-tile">
            <form class="w-full" action="{{ route('system-services.update', compact('systemService')) }}" method="POST">
                @csrf
                @method('PATCH')
                <h2 class="mb-6 font-extrabold break-words">Edit {{ $systemService->name() }}</h2>
                <div class="form-multi-row">
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'service')
                            @slot('placeholder', 'mysql')
                            @slot('required', true)
                            @slot('value', $systemService->service)
                            The system service that should be monitored.
                        @endcomponent
                    </div>
                    <div class="form-group md:w-1/2">
                        @component('layout.components.input')
                            @slot('name', 'name')
                            @slot('label', 'Pretty Name')
                            @slot('placeholder', 'MySQL')
                            @slot('value', $systemService->name)
                            You may specify a prettier name for the service that will be shown in the web interface.
                        @endcomponent
                    </div>
                </div>
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Edit</button>
                    <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                    <a class="ml-auto btn-link hover:text-red" href="#" @click.prevent="showPopupModal = true">Delete</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('modal')
    @component('layout.components.deleteModal')
        @slot('name', $systemService->name())
        @slot('route', route('system-services.destroy', compact('systemService')))
    @endcomponent
@endsection
