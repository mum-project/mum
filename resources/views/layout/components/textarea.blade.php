{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('layout.components.textarea')
    |     @slot('name', 'my_input_name')            <- required
    |     @slot('id', 'my_html_id')                 <- optional, fallback is name
    |     @slot('label', 'Input Label')             <- optional, fallback is name
    |     @slot('required', true)                   <- optional
    |     @slot('hideRequired', true)               <- optional, don't display
    |                                                   red star after label
    |     @slot('addon', 'GiB')                     <- optional, shown at the
    |                                                   right-hand side of the input
    |     @slot('extraProps', 'step="1" min="0"')   <- optional, raw HTML output
    |     @slot('value', '42')                      <- optional, gets overwritten
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
@if ( ! empty($addon))
    <div class="mb-3 form-addon-wrapper{{ $errors->first($name) && !empty($addon) ? ' border-red' : '' }}">
        @endif
        <textarea
                class="{{ empty($addon) ? 'mb-3 form-input' : 'form-addon-input text-right' }}{{ $errors->first($name) && empty($addon) ? ' border-red' : '' }}"
                id="{{ $id ?? $name }}"
                name="{{ $name }}"
                @if ( ! empty($placeholder))
                placeholder="{{ $placeholder ?? '' }}"
                @endif
                @if ($slot != '')
                aria-describedby="{{ $id ?? $name . '-form-help' }}"
                @endif
                @if (!empty($required) && $required)
                required
                @endif
                {!! $extraProps ?? '' !!}
        >{{ old($name) ?: (!empty($value) ? $value : '') }}</textarea>
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