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
<div id="root" class="font-sans antialiased h-screen flex flex-col md:flex-row bg-blue-darkest">
    <div class="flex flex-col h-screen md:flex-row w-full z-20">
        @include('layout.sidebar')
        <div class="flex-1 flex flex-col bg-white overflow-hidden">
            @include('layout.topbar')
            <div class="sm:px-3 py-4 flex flex-1 flex-col items-center overflow-y-auto bg-grey-lighter z-10">
                @include('layout.messages')
                @yield('content')
            </div>
        </div>
    </div>
    <transition name="fade">
        <div v-if="showPopupModal" v-cloak
             class="absolute pin-x pin-y flex flex-row items-center z-40"
             style="background-color: rgba(0,0,0,0.2);">
        </div>
    </transition>
    <transition name="slideUp">
    <div v-if="showPopupModal" v-cloak @click.prevent="showPopupModal = false; setModalContentIdentifier(null)"
             class="absolute pin-x pin-y flex flex-row items-center z-40">
            <div v-if="modalContentIdentifier != null" class="mx-auto block" @click.stop>
                <modal-content-provider
                        :modal-content-identifier="modalContentIdentifier"
                        :modal-content-payload="modalContentPayload"
                        @close="setModalContentIdentifier(null)"
                ></modal-content-provider>
            </div>
            <div v-else class="mx-auto" @click.stop>@yield('modal')</div>
        </div>
    </transition>
</div>
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>