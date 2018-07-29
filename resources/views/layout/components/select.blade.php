{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('layout.components.select')
    |     @slot('name', 'my_input_name')            <- required
    |     @slot('options', ['Foobar', ['label' => 'Foo', 'value' => 'bar']])
    |                                               ^- required
    |     @slot('id', 'my_html_id')                 <- optional, fallback is name
    |     @slot('label', 'Input Label')             <- optional, fallback is name
    |     @slot('required', true)                   <- optional
    |     @slot('placeholder', 'Please select')     <- optional
    |     @slot('inputExtraClass', '')              <- optional
    |     @slot('hideRequired', true)               <- optional, don't display
    |                                                   red star after label
    |     @slot('addon', 'GiB')                     <- optional, shown at the
    |                                                   right-hand side of the input
    |     @slot('extraProps', 'step="1" min="0"')   <- optional, raw HTML output
    |     @slot('selected', '1')                    <- optional, gets overwritten
    |                                                   by old input on redirects
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
        <div class="{{ empty($addon) ? 'mb-3 ' : '' }}relative w-full">
        <select
                class="{{ empty($addon) ? 'form-input' : 'form-addon-input' }}{{ !empty($addon) && empty($align) || !empty($align) && $align === 'right' ? ' text-right' : '' }}{{ $errors->first($name) && empty($addon) ? ' border-red' : '' }} {{ isset($inputExtraClass) ? ' ' . $inputExtraClass : '' }}"
                id="{{ $id ?? $name }}"
                name="{{ $name }}"
                @if ($slot != '')
                aria-describedby="{{ $id ?? $name . '-form-help' }}"
                @endif
                @if (!empty($required) && $required)
                    required
                @endif
                {!! $extraProps ?? '' !!}
        >
            @if (!empty($placeholder))
                <option selected disabled>{{ $placeholder }}</option>
            @endif
            @foreach($options as $option)
                <option
                        value="{{ is_array($option) ? $option['value'] : $option }}"
                        @if (!empty($selected) && $selected == (is_array($option) ? $option['value'] : $option) || is_array($option) && !empty($option['selected']) || old($name) && old($name) == (is_array($option) ? $option['value'] : $option))
                            selected
                        @endif
                        @if (is_array($option) && !empty($option['disabled']))
                            disabled
                        @endif
                >{{ is_array($option) ? $option['label'] : $option }}</option>
            @endforeach
        </select>
            <div class="pointer-events-none absolute pin-y pin-r flex items-center px-2 text-grey-darker">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                </svg>
            </div>
        </div>
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