<template>

    <div v-if="mode === 'view'" class="img-container d-inline-block" :style="containerStyle" ref="image">
        <img :src="url" :style="imgStyle"  @click="$emit('img-clicked')">
    </div>

    <ui-image-adjust v-else :src="src"></ui-image-adjust>

</template>

<style scoped>
.img-container {
    position: relative;
    width: 100%;
    height:100%;
    background-color:#f0f2f5;

}
img {
    width: 100%;
}

</style>

<script>

import { useResizeObserver } from '@vueuse/core'

export default {
    name: 'ui-img-dynamic',
    data() {
        return {
            containerSize: null,
            mounted: null,
            placeholderImage: '/img/profile-pic.jpg'
        }    
    },
    computed: {
        containerStyle: function () {

            if (this.heightRatio == null) {
                return {}
            }

            return {
                height: (this.size * this.heightRatio) + 'px'
            }
        },
        url: function () {
            let h = null
            if (this.heightRatio != null) {
                h = Math.round(this.heightRatio * this.fileSize)
            }
            let w = this.fileSize
            let pixelate = 0
            let source

            // if this.src starts with https://
            if (this.src.startsWith('https://')) {
                source = this.src
            } else {
                source = this.$config.platform.img_storage_url + '/' + this.src
            }

            let url = 'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb' + ',w_' + Math.round(w * 1.5) + ',e_pixelate:' + pixelate

            if (h) {
                url = url + ',h_' + Math.round(h * 1.5)
            }
            if (this.transformations) {
                url = url + ',' + this.transformations
            }
            url = url + '/' + source
            return url

        },
        size: function () {
            if (this.containerSize) {
                return this.containerSize
            }
            if (this.mounted) {
                return this.$refs.image.offsetWidth
            } 
            return null
        },
        fileSize: function () {
            let size = this.size
            let increments = [300, 400, 600, 1000, 2000]
            let incrementsBiggerOrEqualThanSize = increments.filter(increment => {
                return increment >= size
            })
            return Math.min(...incrementsBiggerOrEqualThanSize)
        },
    },
    props: {
        placerholder: {
            required: false,
            default: false
        },
        imgStyle: {
            required: false,
            default: function () {
                return {
                    width: '100%'
                }
            }
        },
        transformations: {
            required: false,
            default: ''
        },
        heightRatio: {
            required: false,
            default: 0.5625
        },
        src: {
            type: String
        },
        mode: {
            type: String,
            required: false,
            default: 'view'
        }
    },
    mixins: [
    ],
    notifications: {
    },
    methods: {
        handleResize ({ width, height }) {
            this.containerSize = width
        }
    },
    beforeDestroy () {
    },
    mounted: function () {
        this.mounted = true

        useResizeObserver(this.$refs.image, (entries) => {
            this.handleResize(entries[0].contentRect)
        })


    },
    created: function() {

    }

}
</script>