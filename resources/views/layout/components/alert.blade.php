{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('layout.components.alert')
    |     @slot('type', 'error|warning|success|info')               <- required
    |     @slot('session', 'status')                                <- optional
    |     @slot('classes' 'my-3 mx-3')                              <- optional
    | @endcomponent
    |
--}}
@if (session(!empty($session) ? $session : $type))
    <mu-alert
            v-cloak
            type="{{ $type }}"
            @if (!empty($classes))
            class="{{ $classes }}"
            @endif
            @if (is_array(session(!empty($session) ? $session : $type)) && array_key_exists('title', session(!empty($session) ? $session : $type)))
            title="{{ session(!empty($session) ? $session : $type)['title'] }}"
            @endif
            @if ($type == 'error')
            :dismissible="false"
            @endif
    >
        @if (is_array(session(!empty($session) ? $session : $type)) && count(array_except(session(!empty($session) ? $session : $type), 'title')) > 1)
            <ul class="p-0 pl-6">
                @foreach(array_except(session(!empty($session) ? $session : $type), 'title') as $value)
                    <li>{{ $value }}</li>
                @endforeach
            </ul>
        @elseif (is_array(session(!empty($session) ? $session : $type)))
            {{ array_except(session(!empty($session) ? $session : $type), 'title')[0] }}
        @else
            {{ session(!empty($session) ? $session : $type) }}
        @endif
    </mu-alert>
@endif