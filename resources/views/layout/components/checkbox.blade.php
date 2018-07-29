{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('layout.components.input')
    |     @slot('name', 'my_input_name')            <- required
    |     @slot('id', 'my_html_id')                 <- optional, fallback is name
    |     @slot('label', 'Input Label')             <- optional, fallback is name
    |     @slot('checked', true)                    <- optional
    |     My helpful description below the input.   <- optional
    | @endcomponent
    |
--}}


<div class="mb-3">
    <input type="hidden" name="{{ $name }}" value="0">
    <label class="checkbox-label">
        {{ $label ?? $name }}
        <input type="checkbox"
               id="{{ $id ?? $name }}"
               name="{{ $name }}"
               value="1"
               @if (old($name) || !empty($checked) && $checked)
                   checked
               @endif
        >
        <span class="checkmark"></span>
    </label>
</div>
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