{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('layout.components.breadcrumbs')
    |     @slot('links', ['Label' => '/my/url', 'Label Only'])      <- required
    | @endcomponent
    |
--}}
<nav>
    <ol class="list-reset flex text-sm">
        @foreach($links as $key => $value)
            <li>
                @if ( ! is_int($key))
                    <a href="{{ $value }}" class="text-blue-lighter opacity-50 no-underline">{{ $key }}</a>
                @else
                    {{ $value }}
                @endif
            </li>
            @if ( ! $loop->last)
                <li><span class="mx-2 text-blue-lighter opacity-50">/</span></li>
            @endif
        @endforeach
    </ol>
</nav>