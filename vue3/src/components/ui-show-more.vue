<template>
    <div class="outer-content" :style="{maxHeight: effectiveMaxHeight}" ref="outer" :class="{'hide-more': hideMore}">

        <div class="inner-content" ref="inner">
            <slot>


            </slot>

        </div>
    </div>
    <div v-if="hideMore" class="d-flex justify-content-center">
        <ui-button  @click="showMore()" v-if="buttonShow" size="sm" class="mb-3">{{ $trans('Show all') }}</ui-button>
    </div>


</template>

<style scoped>

.outer-content {

}

.hide-more {

    mask-image: linear-gradient(0deg, transparent 0.5%, black 25%);
    overflow:hidden;

}

</style>



<script>

import { useResizeObserver } from '@vueuse/core'

export default {

    name: 'ui-show-more',
    data() {
        return {
            outerHeight: null,
            innerHeight: null,
            showAll: false
        }
    },
    props: {
        buttonShow: {
            type: Boolean,
            default: true
        },
        maxHeight: {
            type: Number,
            default: 500
        }
    },
    methods: {
        showMore: function () {
            this.showAll = true
        }
    },
    computed: {
        effectiveMaxHeight: function () {
            if (this.showAll) {
                return 'none'
            }
            return this.maxHeight + 'px'
        },
        hideMore: function () {
            if (this.showAll) {
                return false
            }
            return this.outerHeight < this.innerHeight
        }
    },
    mounted: function () {

        useResizeObserver(this.$refs.outer, (entries) => {
            this.outerHeight = entries[0].contentRect.height
        })
        useResizeObserver(this.$refs.inner, (entries) => {
            this.innerHeight = entries[0].contentRect.height
        })


    }    

}

</script>