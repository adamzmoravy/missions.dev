<template>

    <div class="lightbox" @keyup.esc="closeLightBox" tabindex="0">

        <img ref="lightbox-image" :src="lightboxImagePath" class="lightbox-image clickable" @click="nextLightboxImage">

        <div class="close-modal-button d-flex p-1 m-3" @click="closeLightBox">
          <img class="close-modal-x" src="./img/close.png">
         </div>
    </div>

</template>

<style scoped>


.lightbox {
    display:flex;
    justify-content:center;
    align-items:center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    z-index: 999999;
}

.lightbox-image {
    max-width:100%;
    max-height:100vh;
    object-fit:contain;
}

.close-modal-button {
    border-color:black;
    background-color:white;
    z-index: 99999991;
}

</style>

<script>


import { onClickOutside } from '@vueuse/core'

export default {
    name: 'ui-image-lightbox',
    data() {
        return {
            imageOrientation: 'landscape'
        }
    },
    props: {
        lightboxImageIndexProp: {
            required: true,
            type: Number
        },
        images: {
            required: true,
            type: Array
        }
    },
    computed: {
        lightboxImagePath: function () {
            let path = 'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_1200/'
            path += this.$config.platform.img_storage_url
            path += '/'
            path += this.lightboxImage
            return path
        },
        lightboxImage: function () {
            return this.images[this.lightboxImageIndexProp].filename
        }
    },
    methods: {
        setImageOrientation: function () {
            let img = this.$refs['lightbox-image']
            if (img == null) {
                return 'landscape'
            }
            if (img.naturalWidth > img.naturalHeight) {
                return 'landscape'
            }
            return 'portrait'
        },
        changeLightBoxImageIndex: function (index) {
            this.$emit('lightbox-image-index-change', { index: index })
        },
        closeLightBox: function () {
            this.changeLightBoxImageIndex(null)
        },
        prevLightBoxImage: function () {

            if (this.lightboxImageIndexProp == 0) {

                this.changeLightBoxImageIndex(this.images.length - 1)

            } else {

                this.changeLightBoxImageIndex(this.lightboxImageIndexProp - 1)

            }

        },
        nextLightboxImage: function () {
            this.setImageOrientation()

            if (this.lightboxImageIndexProp == this.images.length - 1) {

                this.changeLightBoxImageIndex(0)

            } else {
                this.changeLightBoxImageIndex(this.lightboxImageIndexProp + 1)
            }

        }
    },
    mounted() {

        let clickOutsideElement = this.$refs['lightbox-image']
        onClickOutside(clickOutsideElement, () => {
            this.closeLightBox()
        })

        clickOutsideElement.focus()

        document.addEventListener('keyup', (e) => {

            if (this.lightboxImageIndex === null) {
                return
            }
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                this.nextLightboxImage();
            }
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                this.prevLightBoxImage();
            }            
            if (e.key === 'Escape') {
                this.closeLightBox()
            }

            e.preventDefault()

        })
    }
}

</script>