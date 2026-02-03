<template>
    <Teleport to="body">

        <transition name="modal" v-if="open">

                <div tabindex="-1" @keydown.esc="$emit('close')" ref="modal" class="modal show fade modal-active" :class="{'modal-tall': size == 'tall'}" @click="close()">

                        <div class="modal-dialog" ref="content">

                            <div id="modal-content" ref="modalContent" class="modal-content d-flex flex-column" @click.stop>
                                <div class="close-modal-button d-flex p-1" @click="close()">
                                    <img class="close-modal-x" src="./img/close.png">
                                </div>
                                <div class="height-animator" ref="height-animator" :style="{height:height}">
                                    <div ref="modalInner" class="modal-inner-container d-flex flex-column">
                                        <div class="single-modal-header">
                                            <slot name="header">
                                                <p class="large"> {{ title }} </p>
                                                <p v-if="subTitle" class="medium"> {{ subTitle }} </p>
                                                <hr class="mt-0 w-100">

                                            </slot>
                                            <slot name="header-post">
                                            </slot>
                                        </div>
                                        <div class="single-modal-subheader">
                                            <slot name="subheader"></slot>
                                        </div>

                                        <div class="single-modal-body container-fluid flex-fill d-flex">
                                            <slot name="body"></slot>
                                        </div>

                                        <div class="single-modal-footer">
                                            <slot name="footer"></slot>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                </div>

            </transition>
    </Teleport>

</template>



<style scoped>

.height-animator {
    overflow:hidden;
    transition: height 0.5s;  
}

@media (max-width: 960px) {
    .height-animator {
        height:100%!important;
    }
}

.modal-dialog {


}
.single-modal-footer {
    padding-top:1rem;
}

</style>

<script>
import { useResizeObserver } from '@vueuse/core'

export default {
    name: 'ui-modal',
    data() {
        return {
            height: 'auto'
        }
    },
    emits: ['close'],
    methods: {
        close: function() {
            this.$emit('close')
        }
    },
    watch: {
        $latestEvent: function() {
            if (this.$latestEvent.eventName === 'new-page-navigation') {
                this.$emit('close')
            }
        },
        open: function() {
            if (this.open) {
                this.$nextTick(() => {
                    this.$refs.modal.focus()
                })
            }
        }
    },
    components: {
    },
    props: {
        size: {
            type: String,
            required: false,
            default: 'normal'
        },
        open: {
            type: Boolean,
            required: false,
            default: false
        },
        title: {
            required: false,
            default: '',
            type: String
        },
        subTitle: {
            required: false,
            default: null,
            type: String
        }
    },
    mounted() {
        useResizeObserver(this.$refs.modalInner, (entries) => {
                this.height = entries[0].contentRect.height + 'px'
           })

    }
}

</script>