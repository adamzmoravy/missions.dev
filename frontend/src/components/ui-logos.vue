<template>
    <div style="text-align:center;align-items: center;justify-content: center; width:100%;">
            <div class="mx-auto logo-container">
                <div style="display:inline-block;" v-for="logo in elements" class="logo">
                <a :href="logo.link" v-tooltip="logo.title"><img :style="{'width': logo.size + '%'}" :src="processingProxy + logo.img"></a>
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        name: 'ui-logos',
        data() {
            return {
                something: 'else'
            }
        },
        props: {
            processingProxy: {
                required: false,
                type: String,
                default: 'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_100,e_pixelate:0/'
            },
            config: {
                required: true,
                type: Object
            },
            elements: {
                required: true,
                type: Array
            }
        },
        mixins: [
        ],
        computed: {
        },
        methods: {
        },
        created: function() {
        },
        mounted: function() {
        }
    }
</script>

<style scoped>
.logo-container {
    max-width:850px;
    }
    .logo {
        width:80px;
        margin-right:35px;
        margin-top:25px;
    }
</style>
