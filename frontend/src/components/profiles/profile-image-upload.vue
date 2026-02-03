<template>
    <div class="row">

        <div class="col-md-6 d-flex align-items-center">

            <ui-image-upload class="mt-3" :button-label="$trans('Choose image')" @uploading="uploading" @uploaded="saveImage" ref="image-upload" />

        </div>
        <div class="col-md-6">

            <profile-image-simple style="width:90%;" @img-loaded="status='loaded'" :profile="profile" :status="status"/>

        </div>            

    </div>
</template>

<script>

//import { Emit } from 'vue-property-decorator';
import backend from '../../backend.js'

export default {
    name: 'profile-image-upload',
    data: () => {
        return {
            status: '',
            images: null,
        }
    },
    props: {
        profile: {
            required: true,
            type: Object
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
        uploading() {
            console.log('uploading')
        },
        saveImage(data) {
            console.log('saveImage', data)
            this.status = 'uploading'
            let body = {
                    'image_collection': {
                        'profile': {
                            'filename': data.image.filename
                        } 
                    }
                }
            backend.patch('/users/me/profile/image_collection', body).then(response => {
                this.status = ''
                this.$emit('uploaded')

            })

        },
        imageChanged() {
            this.status = 'uploading'
            this.$emit('uploading')
            this.images = this.$refs.imageInputRef.files[0];
            const formData = new FormData()
            formData.append('file', this.$refs.imageInputRef.files[0]);
            backend.postFile('/files/images/', formData).then(response => {
                let body = {
                    'image_collection': {
                        'profile': {
                            'filename': response.data.image.filename
                        } 
                    }
                }
                backend.patch('/users/me/profile/image_collection', body).then(response => {

                    this.status = 'uploaded'
                    this.$emit('uploaded')

                })
            })
            //            let updateResults = backend.patch('/users/me/profile/image_collection', body)
        }, 

    },
}

</script>