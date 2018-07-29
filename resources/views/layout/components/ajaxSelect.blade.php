{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('layout.components.input')
    |     @slot('name', 'my_input_name')            <- required
    |     @slot('id', 'my_html_id')                 <- optional, fallback is name
    |     @slot('label', 'Input Label')             <- optional, fallback is name
    |     @slot('required', true)                   <- optional
    |     @slot('hideRequired', true)               <- optional, don't display
    |                                                   red star after label
    |     @slot('apiUrl', '/domains')               <- required, use absolute path
    |     @slot('mapApiValues', 'function (options, data) { options.push({ value: data.v, label: data.l }) }')
                                                    ^- required, JS function to
                                                        map API data to the select
                                                        option label and value
    |     @slot('selected', '42')                      <- optional, gets overwritten
                                                        by old input on redirects
    |     My helpful description below the input.   <- optional
    | @endcomponent
    |
--}}
<label
        class="mb-2 form-label"
        for="{{ $id ?? $name }}"
>{{ $label ?? $name }}
    @if (!empty($required) && $required && empty($hideRequired))
        <span class="text-red">*</span>
    @endif
</label>
<ajax-select
        class="mb-3 {{ $errors->first($name) ? ' border-red' : '' }}"
        id="{{ $id ?? $name }}"
        name="{{ $name }}"
        type="{{ $type ?? 'text' }}"
        @if ( ! empty($placeholder))
        placeholder="{{ $placeholder ?? '' }}"
        @endif
        @if ($slot != '')
        aria-describedby="{{ $id ?? $name . '-form-help' }}"
        @endif
        @if (!empty($required) && $required)
        required
        @endif
        api-url="{{ $apiUrl }}"
        :map-api-values="{{ $mapApiValues }}"
        @if (!empty($selected) || old($name))
        selected-value="{{ old($name) ?: $selected }}"
        @endif
></ajax-select>
@if ( ! empty($addon))
    <div class="form-addon">{{ $addon }}</div>
    </div>
@endif
@if ($errors->has($name))
    <p
            class="form-help text-red"
    >{{ $errors->first($name) }}</p>
@endif
@if ($slot != '')
    <p
            class="form-help"
            id="{{ $id ?? $name . '-form-help' }}"
    >{{ $slot }}</p>
@endif