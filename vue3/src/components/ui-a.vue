<css scoped>

</css>

<template>

    <a :href="url" @click="goToRoute()" class="ui-a">
        <slot></slot>

    </a>

</template>

<script>
import { useWindowScroll } from '@vueuse/core'

export default {
        name: 'ui-a',
        props: {
            href: {
                required: false,
                default: false
            },
        },
        methods: {
            goToRoute() {
                this.$emit('click')
                if (this.href) {
                    this.$router.push(this.route).catch(err => {
                        if (
                            err.name !== 'NavigationDuplicated' &&
                            !err.message.includes('Avoided redundant navigation to current location')
                            ) {
//                                this.$root.eventBus.$emit('new-page-navigation')
                                logError(err)
                            } else {
//                                this.$root.eventBus.$emit('new-page-navigation')
                            }
                    })

                }
            }
        },
        computed: {
            route: function() {
                return '/' + this.lang + this.href
            },
            url: function() {
                return null
                return '/' + this.lang + this.href
            },
            lang: function() {
                return this.$lang
            }
        },
        data() {
            return {
            }
        }
    }


</script>