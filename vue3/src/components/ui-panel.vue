<template>

    <div class="content-container">

        <slot> </slot>

    </div>

</template>

<style scoped>

.content-container {
    padding:1em;
    border-radius:6px;
    border-color:lightgrey;
    border-style:solid;
    border-width:1px;
}    

</style>

<script>

export default {
    name: 'ui-panel'
}

</script>