@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Home' => route('home'), 'Integrations' => route('integrations.index'), '#' . $integration->id => route('integrations.show', compact('integration')), 'Edit'])
    @endcomponent
@endsection

@section('content')
    <div class="max-w-lg w-full">
        <integrations-form model-class="{{ $integration->model_class }}">
            <template slot-scope="{modelClass, availablePlaceholders}">
                <div class="dashboard-tile">
                    <form class="w-full" action="{{ route('integrations.update', compact('integration')) }}"
                          method="POST">
                        @csrf
                        @method('PATCH')
                        <h2 class="mb-6 font-extrabold break-words">Update Integration #{{ $integration->id }}</h2>
                        <div class="form-multi-row">
                            <div class="form-group md:w-2/3">
                                @component('layout.components.input')
                                    @slot('name', 'name')
                                    @slot('placeholder', 'Integration #' . $integration->id)
                                    @slot('value', $integration->name)
                                    Specify a name for the integration, maybe something to help you remember what it
                                    is
                                    used
                                    for.
                                @endcomponent
                            </div>
                            <div class="form-group md:w-1/3">
                                <label class="mb-2 form-label">Type</label>
                                <div class="py-3 mb-3 border-transparent border">
                                    @if ($integration->type === App\ShellCommandIntegration::class)
                                        Shell Command
                                    @elseif ($integration->type === App\WebHookIntegration::class)
                                        Webhook
                                    @endif
                                </div>
                                <p class="form-help">For technical reasons, you cannot change the type of an
                                    integration once it is created.</p>
                            </div>
                        </div>
                        <div class="form-multi-row">
                            <div class="form-group md:w-1/2">
                                @component('layout.components.select')
                                    @slot('name', 'model_class')
                                    @slot('options', $availableModelOptions)
                                    @slot('label', 'Model')
                                    @slot('required', true)
                                    @slot('selected', $integration->model_class)
                                    @slot('extraProps', 'v-model="modelClass.value"')
                                    Please select a model that should trigger the integration.
                                @endcomponent
                            </div>
                            <div class="form-group md:w-1/2">
                                @component('layout.components.select')
                                    @slot('name', 'event_type')
                                    @slot('options', $eventTypeOptions)
                                    @slot('label', 'Event')
                                    @slot('required', true)
                                    @slot('selected', $integration->event_type)
                                    Please select an event that should trigger the integration.
                                @endcomponent
                            </div>
                        </div>
                        @if ($integration->type === App\ShellCommandIntegration::class)
                            <div class="form-row">
                                @component('layout.components.select')
                                    @slot('name', 'value')
                                    @slot('options', $shellCommandOptions)
                                    @slot('label', 'Shell Command')
                                    @slot('required', true)
                                    @slot('inputExtraClass', 'text-sm font-mono')
                                    @slot('selected', $integration->value)
                                    Please select a shell command that should be triggered. You can configure these
                                    in your local <code>.env</code> file.
                                @endcomponent
                            </div>
                            @if ($shellParametersEnabled)
                                <div class="form-row">
                                    <edit-integration-parameters
                                            :available-placeholders="availablePlaceholders"
                                            :old-parameters="{{ json_encode(old('parameters') ?? $integrationParameters) }}"
                                            @set-modal-content-identifier="setModalContentIdentifier"
                                            @set-modal-content-payload="setModalContentPayload"
                                    ></edit-integration-parameters>
                                    @if ($errors->has('parameters'))
                                        <p class="mt-2 form-help text-red">{{ $errors->first('parameters') }}</p>
                                    @endif
                                </div>
                            @endif
                        @elseif ($integration->type === App\WebHookIntegration::class)
                            <div class="form-row">
                                @component('layout.components.input')
                                    @slot('name', 'value')
                                    @slot('label', 'URL')
                                    @slot('required', true)
                                    @slot('inputExtraClass', 'font-mono text-sm')
                                    @slot('value', $integration->value)
                                    Specify a webhook URL that should be called when the integration is triggered.
                                    You may use placeholders in this field.
                                @endcomponent
                            </div>
                            <div class="form-row text-grey-dark" v-if="availablePlaceholders != null">
                                <p>Available Placeholders: <code
                                            class="inline-code mr-2 my-1"
                                            v-for="placeholder in availablePlaceholders">@{{
                                        placeholder }}</code></p>
                            </div>
                        @endif
                        <div class="form-row">
                            @component('layout.components.checkbox')
                                @slot('name', 'active')
                                @slot('label', 'Active')
                                @slot('checked', $integration->active)
                                Should the integration be enabled?
                            @endcomponent
                        </div>
                        <div class="form-footer">
                            <button class="btn btn-primary" type="submit">Save Changes</button>
                            <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                            <a class="btn-link ml-auto hover:text-red" href="#"
                               @click.prevent="showPopupModal = true">Delete</a>
                        </div>
                    </form>
                </div>
            </template>
        </integrations-form>
    </div>
@endsection

@section('modal')
    @component('layout.components.deleteModal')
        @slot('name', 'Integration #' . $integration->id)
        @slot('route', route('integrations.destroy', compact('integration')))
    @endcomponent
@endsection
