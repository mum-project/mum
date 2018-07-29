@extends('layout.master')

@section('breadcrumbs')
    @component('layout.components.breadcrumbs')
        @slot('links', ['Home' => route('home'), 'Integrations' => route('integrations.index'), 'Create'])
    @endcomponent
@endsection

@section('content')

    <div class="max-w-lg w-full">
        @if (empty($availableIntegrationTypeOptions))
            <div class="dashboard-tile">
                <h2 class="mb-6 font-extrabold break-words">Create Integration</h2>
                <div class="text-grey-dark mt-8 mb-4 py-8 text-center leading-normal">
                    All available integration types are disabled.<br>
                    Please enable at least one type to create a new integration.<br>
                    You can change the integration configuration in your <code>.env</code> file.
                </div>
            </div>
        @else
            <integrations-form
                    model-class="{{ old('model_class') }}"
                    integration-type="{{ old('type') }}">
                <template slot-scope="{modelClass, availablePlaceholders, integrationType}">
                    <div class="dashboard-tile">
                        <form class="w-full" action="{{ route('integrations.store') }}"
                              method="POST">
                            @csrf
                            <h2 class="mb-6 font-extrabold break-words">Create Integration</h2>
                            <div class="form-multi-row">
                                <div class="form-group md:w-2/3">
                                    @component('layout.components.input')
                                        @slot('name', 'name')
                                        @slot('placeholder', 'My Integration')
                                        Specify a name for the integration, maybe something to help you remember what it
                                        is used for.
                                    @endcomponent
                                </div>
                                <div class="form-group md:w-1/3">
                                    @component('layout.components.select')
                                        @slot('name', 'type')
                                        @slot('options', $availableIntegrationTypeOptions)
                                        @slot('label', 'Type')
                                        @slot('required', true)
                                        @slot('placeholder', 'Please select...')
                                        @slot('extraProps', 'v-model="integrationType.value"')
                                        Please select the type of integration that you want to create.
                                    @endcomponent
                                </div>
                            </div>
                            <div class="form-multi-row">
                                <div class="form-group md:w-1/2">
                                    @component('layout.components.select')
                                        @slot('name', 'model_class')
                                        @slot('options', $availableModelOptions)
                                        @slot('label', 'Model')
                                        @slot('required', true)
                                        @slot('selected', old('model_class'))
                                        @slot('placeholder', 'Please select...')
                                        @slot('extraProps', 'v-model="modelClass.value"')
                                        Please select a model that should trigger the integration.
                                    @endcomponent
                                </div>
                                <div class="form-group md:w-1/2">
                                    @component('layout.components.select')
                                        @slot('name', 'event_type')
                                        @slot('options', $eventTypeOptions)
                                        @slot('label', 'Event')
                                        @slot('selected', old('event_type'))
                                        @slot('required', true)
                                        Please select an event that should trigger the integration.
                                    @endcomponent
                                </div>
                            </div>
                            <div v-if="integrationType.value === 'shell_command'" v-cloak>
                                @if (empty($shellCommandOptions))
                                    <div class="form-row">
                                        <div class="text-grey-dark my-4 text-center leading-normal">
                                            You have to configure at least one shell command in your <code>.env</code>
                                            file.
                                        </div>
                                    </div>
                                @else
                                    <div class="form-row">
                                        @component('layout.components.select')
                                            @slot('name', 'value')
                                            @slot('options', $shellCommandOptions)
                                            @slot('label', 'Shell Command')
                                            @slot('required', true)
                                            @slot('placeholder', ' ')
                                            @slot('inputExtraClass', 'text-sm font-mono')
                                            Please select a shell command that should be triggered. You can configure
                                            these
                                            in your local <code>.env</code> file.
                                        @endcomponent
                                    </div>
                                    @if ($shellParametersEnabled)
                                        <div class="form-row">
                                            <edit-integration-parameters
                                                    :available-placeholders="availablePlaceholders"
                                                    @set-modal-content-identifier="setModalContentIdentifier"
                                                    @set-modal-content-payload="setModalContentPayload"
                                            ></edit-integration-parameters>
                                            @if ($errors->has('parameters'))
                                                <p class="mt-2 form-help text-red">{{ $errors->first('parameters') }}</p>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div v-else-if="integrationType.value === 'web_hook'" v-cloak>
                                <div class="form-row">
                                    @component('layout.components.input')
                                        @slot('name', 'value')
                                        @slot('label', 'URL')
                                        @slot('required', true)
                                        @slot('placeholder', 'https://example.com/my-webhook')
                                        @slot('inputExtraClass', 'font-mono text-sm')
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
                            </div>
                            <div v-else class="form-row">
                                <div class="text-grey-dark my-4 text-center leading-normal">
                                    Please select an integration type to see all required inputs.
                                </div>
                            </div>
                            <div class="form-row">
                                @component('layout.components.checkbox')
                                    @slot('name', 'active')
                                    @slot('label', 'Active')
                                    @slot('checked', true)
                                    Should the integration be enabled?
                                @endcomponent
                            </div>
                            <div class="form-footer">
                                <button
                                        class="btn btn-primary"
                                        type="submit"
                                        :disabled="integrationType.value == null || {{ json_encode($shellCommandOptions) }}.length == 0 && integrationType.value === 'shell_command'"
                                >Create Integration
                                </button>
                                <a class="btn-link ml-4 mr-auto" href="{{ url()->previous() }}">Cancel</a>
                            </div>
                        </form>
                    </div>
                </template>
            </integrations-form>
        @endif
    </div>
@endsection
