<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ url('/') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
@include('layout.noscript')
<div id="root" class="font-sans antialiased h-screen flex bg-blue-darkest">
    <div class="flex flex-1 flex-col justify-center items-center px-3">
        <div class="flex flex-col max-w-lg w-full items-center mx-auto">
            @yield('content')
        </div>
    </div>
</div>
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>