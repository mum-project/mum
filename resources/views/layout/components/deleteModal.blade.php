{{--
    |--------------------------------------------------------------------------
    | How to use this component
    |--------------------------------------------------------------------------
    |
    | @component('layout.components.deleteModal')
    |     @slot('name', $model->name)                           <- required
    |     @slot('route', route(...))                            <- required
    |     @slot('noNameFormatting', true)                       <- optional
    |     @slot('unsafeHtml', true)                             <- optional
    | @endcomponent
    |
--}}
<popup-modal v-if="showPopupModal" @close="showPopupModal = false">
    <h2 class="font-extrabold mb-6">Are you sure?</h2>
    <p class="mb-6 leading-normal">
        You are about to delete @if (!isset($noNameFormatting))"<span class="font-bold">@endif{!! isset($unsafeHtml) && $unsafeHtml ? $name : htmlspecialchars($name) !!}@if (!isset($noNameFormatting))</span>"@endif.<br>
        This action is irreversible, so maybe you want to think about it for a second.
    </p>
    <form method="POST" action="{{ $route }}">
        @method('DELETE')
        @csrf
        <div class="flex flex-row items-center">
            <a class="btn-link ml-auto mr-3" href="#" @click.prevent="showPopupModal = false">Cancel</a>
            <button class="btn bg-red hover:bg-red-dark text-white" type="submit">Yes, Delete it!</button>
        </div>
    </form>
</popup-modal>