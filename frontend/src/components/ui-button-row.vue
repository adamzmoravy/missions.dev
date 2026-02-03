<template>

    <div class="button-row">
        <slot>
        </slot>
    </div>

</template>

<style scoped>

    .button-row {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.3em;       
    }

</style>

<script>

export default {
    name: 'ui-button-row'
}

</script>