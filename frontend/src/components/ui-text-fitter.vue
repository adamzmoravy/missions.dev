<template>
    <span v-bind:style="css">{{text}}</span>    
</template>

<script>
    export default {
        name: 'text-fitter',
        data() {
            return {
                something: 'else'
            }
        },
        props: {
            text: {
                required: true,
                type: String
            },
            baseLength: {
                required: true,
                default: 15
            },
            maxSizePercent: {
                required: false,
                default: 100
            },
            minSizePercent: {
                required: false,
                default: 20
            }
        },
        mixins: [
        ],
        computed: {
            charNumber: function() {
                return this.text.length
            },
            sizePercent: function () {
                return (this.baseLength / this.charNumber) * 100
            },
            calculatedSize: function () {
                if (this.sizePercent > this.maxSizePercent) {
                    return this.maxSizePercent
                }
                else if (this.sizePercent < this.minSizePercent) {
                    return this.minSizePercent
                }
                else {
                    return this.sizePercent
                }
            },
            css: function () {
                return {
                    fontSize: this.calculatedSize + '%'
                }
            }
        },
        methods: {
        },
        created: function() {
        },
        mounted: function() {
        }
    }
</script>
