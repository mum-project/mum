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
    |     @slot('minlength', 8)                     <- optional
    |     @slot('hideRequired', true)               <- optional, don't display
    |                                                   red star after label
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
<custom-password
        classes="mb-3{{ $errors->has($name) ? ' border-red' : '' }}"
        id="{{ $id ?? $name }}"
        name="{{ $name }}"
        @if ($slot != '')
        aria-describedby="{{ $id ?? $name . '-form-help' }}"
        @endif
        @if (!empty($required) && $required)
        required
        @endif
        @if (!empty($minlength))
        minlength="{{ $minlength }}"
        @endif
></custom-password>
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