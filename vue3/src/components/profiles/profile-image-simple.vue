<template>

    <div>

        <div :class="{'pink-background': $ui.profileBackground}" class="container-responsive position-relative">
            <img v-if="loading" src="../img/loading-dots.svg" class="loading-img">
            <img :src="profilePicUrl" class="profile-image-responsive">
            <slot name="icon"></slot>
        </div>

    </div>

</template>

<style scoped>
.loading-img {
    position:absolute;
    width:90%;
}
.container-responsive {
    width: 100%;
    display: inline-block;
    line-height: 1!important;
}

.pink-background {
    padding: 5%;
    background: url("/img/profile-background-pink.svg");    
    background-size: cover;

}
</style>

<script>

import placeholderImage from './img/profile-pic.jpg'
    export default {
        name: 'profile-image-simple',
        data() {
            return {
                placeholderImage: placeholderImage
            }
        },
        props: {
            status: {
                required: false,
                default: 'loaded'
            },
            profile: {
                required: true
            },
            width: {
                required: false,
                default: 200
            },
            height: {
                required: false,
                default: 200
            }
        },
        computed: {
            loading: function () {
                if (this.status != 'loaded' && this.status != '') {
                    return true
                }
                return false
            },
            profilePicUrl: function () {
                if (this.profile && this.profile.image) {
                    return 'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,h_' + this.height * 2 + ',w_' + this.width * 2  + '/' + this.profile.image
                } 
                else if (this.profile && this.profile.image_collection) {
                    return 'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,h_' + this.height * 2 + ',w_' + this.width * 2  + '/'  + this.profile.image_collection.profile.url
                }
                else {
                    return this.placeholderImage
                }
            }
        }
    }
</script>
