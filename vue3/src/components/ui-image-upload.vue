<template>
    <div>

        <input class="d-none"
                ref="imageInputRef"
                type="file"
                name="image"
                accept="image/gif, image/jpeg, image/jpg, image/png"
                @change="imageChanged()">


        <slot :upload="upload" :status="status">
            <ui-button button-type="new" :size="size" :working="imageLoading" @click="upload">{{ buttonLabel }}</ui-button>
        </slot>
    </div>
</template>

<script>

import backend from '../backend.js'

export default {
    name: 'ui-image-upload-',
    data: () => {
        return {
            status: '',
            images: null,
        }
    },
    props: {
        buttonLabel: {
            required: false,
            default: 'Upload image',
            type: String
        },
        size: {
            required: false,
            default: 'lg',
            type: String
        }
    },
    computed: {
        imageLoading: function () {
            if (this.status == 'uploading') {
                return true
            }
            return false
        }
    },
    methods: {
        upload () {
            console.log('upload button clicked')
            this.$refs['imageInputRef'].click()
        },
        imageChanged() {
            if (this.$refs.imageInputRef.files[0].size > 1000 * 1000 * 10) {
                alert(this.$trans('Image file size is too big. Max size is 10MB. Make the image smaller and try again.'))
                return
            }

            this.status = 'uploading'
            this.$emit('uploading')
            this.images = this.$refs.imageInputRef.files[0];
            const formData = new FormData()
            formData.append('file', this.$refs.imageInputRef.files[0]);

            backend.postFile('/files/images/', formData).then(response => {
                this.$emit('uploaded', response.data)
                console.log(response)
                this.status = 'loaded'
            })
        }, 

    },
}

</script>