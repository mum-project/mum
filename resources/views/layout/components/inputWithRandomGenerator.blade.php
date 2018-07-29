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
    |     @slot('type', 'email')                    <- optional
    |     @slot('addon', '@')                       <- optional
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
<input-with-random-generator
        classes="mb-3{{ $errors->has($name) ? ' border-red' : '' }}"
        id="{{ $id ?? $name }}"
        name="{{ $name }}"
        @if ($slot != '')
        aria-describedby="{{ $id ?? $name . '-form-help' }}"
        @endif
        @if (!empty($required) && $required)
        required
        @endif
        @if ($errors->has($name))
        validation-error="{{ $errors->first($name) }}"
        @endif
        @if ($slot != '')
        form-help="{{ $slot }}"
        @endif
        @if (old($name) || !empty($value))
        old-value="{{ old($name) ?? $value }}"
        @endif
        @if ( ! empty($addon))
        addon="{{ $addon }}"
        @endif
        @if ( ! empty($type))
        input-type="{{ $type }}"
        @endif
        random-provider="{{ config('mum.random_generator.local_part.provider') }}"
        :diceware-word-count="{{ config('mum.random_generator.local_part.diceware_word_count') }}"
        diceware-separator="{{ config('mum.random_generator.local_part.diceware_separator') }}"
        :insecure-random-char-count="{{ config('mum.random_generator.local_part.insecure_random_char_count') }}"
></input-with-random-generator>