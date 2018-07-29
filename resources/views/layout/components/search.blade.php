{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('layout.components.search')
    |     @slot('outputTextFunction', "r => r.address")             <- required
    |     @slot('resultLinkBaseUrl', route('foo.index'))  <- optional, default:
    |                                                           current route
    |     @slot('apiUrl', route('foo.index')              <- optional, default:
    |                                                           current route
    |     @slot('oldValue', 'foo bar')                    <- optional, default:
    |                                                           request param
    |     @slot('hiddenInputValues', [['name' => 'foo', 'value' => 'bar']])
    |                                                     ^- optional, default:
    |                                                           request param
    | @endcomponent
    |
--}}

<index-search
        :output-text-function="{{ $outputTextFunction }}"
        result-link-url-base="{{ isset($resultLinkBaseUrl) ? $resultLinkBaseUrl : route(Route::currentRouteName()) }}"
        api-url="{{ isset($apiUrl) ? $apiUrl : route(Route::currentRouteName()) }}"
        old-value="{{ isset($oldValue) ? $oldValue : request()->get('search') }}"
        @if (isset($hiddenInputValues))
        :hidden-input-values="{{ json_encode($hiddenInputValues) }}"
        @endif
></index-search>
