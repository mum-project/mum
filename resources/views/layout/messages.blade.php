<div class="max-w-lg w-full">
    @component('layout.components.alert')
        @slot('type', 'error')
        @slot('classes', 'my-3 mx-3')
    @endcomponent
    @component('layout.components.alert')
        @slot('type', 'warning')
        @slot('classes', 'my-3 mx-3')
    @endcomponent
    @component('layout.components.alert')
        @slot('type', 'success')
        @slot('classes', 'my-3 mx-3')
    @endcomponent
    @component('layout.components.alert')
        @slot('type', 'success')
        @slot('session', 'status')
        @slot('classes', 'my-3 mx-3')
    @endcomponent
    @component('layout.components.alert')
        @slot('type', 'info')
        @slot('classes', 'my-3 mx-3')
    @endcomponent
</div>