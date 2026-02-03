<template>

    <span>
         <span v-html="html"></span>
    </span>
</template>

<script>
    export default {
        name: 'language-field',
        data() {
            return {
            }
        },
        props: {
            'fields': {
            },
            'name': {
            },
            'language': {
                default: function () {
                    return this.$parent.$lang
                }
            }
        },
        computed: {
            lang: function () {
                return this.language.toUpperCase()
            },
            html: function () {
                return this.getFallback().html
            }
        },
        mixins: [
        ],
        methods: {
            getFallback () {
                if (this.lang in this.fields) {
                    return this.fields[this.lang][this.name]
                } else {
                    console.log(this.fields[0])
                    return Object.values(this.fields)[0][this.name]
                }
            }
        },
        mounted: function() {
        }
    }
</script>
