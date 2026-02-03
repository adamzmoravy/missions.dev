<template>
    <div class="container-fluid bg-white">
        <div class="container pt-3 pt-md-4 px-0 px-md-3 pb-0">
            <slot>
                
            </slot>
         </div>

    </div>
</template>

<script>

    export default {
        name: 'ui-page-header',
        data() {
            return {
            }
        },
        props: {
        },
        mixins: [
        ],
        notifications: {
        },
        methods: {
            setup: function() {
            }
        },
        beforeDestroy () {
        },
        created: function() {
        },
        mounted: function() {
        }
    }
</script>
