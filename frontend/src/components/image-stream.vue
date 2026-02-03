<template>

    <div class="stream-container">
        <input type="file" ref="file" style="display: none" />

        <vue-horizontal>
            <div class="add-image" v-if="offerAddImage">
                <ui-image-upload @uploaded="addImage" v-slot:default="slotProps">
                    <div @click="slotProps.upload" >
                    <div>
                            <a v-if="slotProps.status != 'uploading'">+ Add annonymous moment</a>
                            <span v-else>Uploading ... </span>
                        </div>
                    </div>
                </ui-image-upload>
            </div>
            <div class="image-container" v-for="(image, i) in images" :key="image.id" @click="lightbox(i)">
                <ui-img-dynamic :transformations="''" class="img-dynamic clickable" :heightRatio="0.75" :src="image.filename" />
            </div>
        </vue-horizontal>

        <ui-modal :open="uploadImageModal" :title="`Upload image`" @close="closeModal">
            <template v-slot:body>
                <div class="pt-2">
                    <p class="medium">JPG, PNG, GIF files under 10 MB are supported.</p>

                    <vue-horizontal>
                        <div class="image-container" v-for="(image, i) in images" :key="image.id" @click="lightbox(i)">
                            <ui-img-dynamic :transformations="'e_grayscale'" class="img-dynamic clickable" :heightRatio="0.75" :src="image.filename" />
                        </div>
                    </vue-horizontal>                    
                    <ui-image-upload @uploaded="addImage" class="mt-3">
                    </ui-image-upload>

                </div>
            </template>
            <template v-slot:footer>
                <div class="d-flex justify-content-center">
                    <ui-button class="ml-2" @click="closeModal">Close</ui-button>
                </div>
            </template>
        </ui-modal>

        <ui-image-lightbox v-if="lightboxImageIndex !== null" :lightbox-image-index-prop="lightboxImageIndex" @lightbox-image-index-change="changeLightBoxImage" :images="images" />
    </div>

</template>

<style scoped>

.add-image div {
    width:130px;

}

.add-image {
    
    /* vertically and horizontally center */
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: white;
    border-top-left-radius:6px;
    border-bottom-left-radius:6px;
    height: 100%;
    text-align: center;
    font-size: 12px;
    color: #999;

}
.image-container {
    margin-left:4px;
    width:23%;
    display:inline-block;

}
.img-dynamic {

}
.clickable {
    cursor:pointer;
}

</style>

<script>
import backend from '../backend'
export default {
    name: 'image-stream',
    data() {
        return {
            lightboxImageIndex: null
        }
    },
    props: {
        offerAddImage: {
            type: Boolean,
            default: true,
            required: false
        },
        uploadImageModal: {
            type: Boolean,
            default: false,
            required: false
        },
        images: {
            type: Array,
            required: false
        },
        postImageUrl: {
            type: String,
            required: true
        }
    },
    methods: {
        closeModal: function () {
            this.$emit('close-upload-dialogue')
        },
        changeLightBoxImage: function (event) {
            this.lightboxImageIndex = event.index
        },
        lightbox: function (index) {
            this.lightboxImageIndex = index
        },
        addImage: function (imageData) {
            let data = {
                filename: imageData.image.filename
            }
            backend.post(this.postImageUrl, data).then((response) => {
                this.$emit('new-image', response.data)
            })
        }
    },
    mounted() {
        this.$emit('register-open-upload-dialogue', this.openUploadDialogue)
    }
}
</script>
