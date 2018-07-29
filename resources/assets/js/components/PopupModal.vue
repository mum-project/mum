<template>
    <div class="bg-white rounded shadow-lg px-8 py-8 mx-auto my-auto w-screen relative max-w-sm overflow-hidden">
        <div class="absolute pin-t pin-r pt-2 pr-4">
            <a class="text-xl text-grey-dark no-underline" href="#" @click.prevent="$emit('close')">&times;</a>
        </div>
        <slot></slot>
    </div>
</template>

<script>
export default {};
</script>

<style>
</style>
