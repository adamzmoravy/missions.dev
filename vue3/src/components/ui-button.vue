<template>

    <component @click="clicked" :disabled="this.working || disabled" :is="$theme($options.name)" type="button">
        <slot></slot>
    </component>

</template>

<script>
export default {
    data() {
        return {
        }
    },
    methods: {
        clicked(e) {

            if (this.href) {
                    this.$router.push('/' + this.$lang  + this.href).catch(err => {
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

            // prevent propagation of the event
            e.stopPropagation()
            if (!this.working) {
                this.$emit('clicked')
            }
        }
    },
    name: 'ui-button',
    props: {
        href: {
            required: false,
            default: false
            },
        disabled: {
            required:false,
            default: false
        },
        size: {
            required:false,
            default: 'lg'
        },
        buttonType: {
            required:false,
            default: 'primary'
        },
        working: {
            required:false,
            default:false
        },
        label: {
            required: false,
            default: '',
            type: String
        }
    }
}

</script>