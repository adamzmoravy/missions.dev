<template>

    <div>
        <vue-horizontal>

            <div class="attachment-container mr-3 mb-3 d-inline-block" v-if="attachments" v-for="attachment in attachments">
                <div class="attachment-inner">
                    <div class=" clickable-container" @click="downloadAttachment(attachment.data.filename)" >
                        <img src="./img/pdf-download.svg" class="file-image">
                        <p @click="downloadAttachment(attachment.data.filename)" class="small mt-2 mb-0">{{ attachment.name }}</p>
                    </div>
                    <div class="small remove" v-if="canUpload">
                        <a @click="removeAttachment(attachment.id)">(remove)</a>
                    </div>

                </div>
            </div>
        </vue-horizontal>

        <div v-if="canUpload">

 
            <ui-modal :open="addAttachment" :title="$trans('Upload document')" @close="closeModal">
 

                <template v-slot:body>

                    <div class="pt-5">
                        <p class="medium">PDF files under 50 MB are supported.</p>

                        <ui-input-text v-model="attachmentName" :placeholder="'Document name'" class="mb-4"></ui-input-text>
                        <input class="d-block mb-4"
                            ref="attachmentInputRef"
                            type="file"
                            name="attachment"
                            accept=".pdf"
                            @change="fileChange"
                        >
                    </div>


                </template>

                <template v-slot:footer>
                    <div class="d-flex justify-content-center">
                        <ui-button button-type="new" :disabled="!readyToUpload" :size="size" :working="attachmentLoading" @click="uploadFile">{{ buttonLabel }}</ui-button>
                        <ui-button class="ml-2" @click="closeModal">Cancel</ui-button>
                    </div>

                </template>


            </ui-modal>


        </div>

    </div>

</template>


<style scoped>
.attachment-container {
    max-width:6rem;
    display:flex;
    flex-direction:column;
    text-align:center;
    align-items: start;

}

.clickable-container {
    display:flex;
    cursor: pointer;    
    flex-direction:column;
    text-align:center;

}

.attachment-inner {
    width:100%;
    display:flex;

    flex-direction:column;
    text-align:center;
    align-items: start;

}

.remove {
    width:100%;
    text-align:center;
    color: #FF0000;
    cursor: pointer;
    text-decoration: underline;
}
.file-image {
    margin-left:auto;
    margin-right:auto;
    width:40%;
}

</style>

<script>

import backend from '../backend.js'

export default {
    name: "attachments",
    data: () => {
        return {
            noFiles: true,
            attachmentName: '',
            status: '',
            images: null,
        }
    },
    props: {
        addAttachment: {
            required: false,
            default: false,
            type: Boolean
        },
        canUpload: {
            required: false,
            default: false,
            type: Boolean
        },
        attachments: {
            required: false,
            default: null,
            type: Array
        },
        uploadUrl: {
            required: true,
            type: String
        },
        removeUrl: {
            required: true,
            type: String
        },
        downloadUrl: {
            required: true,
            type: String
        },
        buttonLabel: {
            required: false,
            default: 'Upload',
            type: String
        },
        size: {
            required: false,
            default: 'lg',
            type: String
        }
    },
    computed: {
        readyToUpload: function () {
            if (this.noFiles) {
                return false
            }
            if (this.attachmentName == "") {
                return false
            }
            return true
        },
        attachmentLoading: function () {
            if (this.status == 'uploading') {
                return true
            }
            return false
        }
    },
    methods: {
        closeModal() {
            this.$emit('closed')
        },
        removeAttachment (id) {
            if (confirm('Are you sure you want to remove this attachment?')) {
                backend.delete(this.removeUrl + id + '/', {
                }).then(response => {
                    console.log(response)
                    this.$emit('removed', id)
                })
            }
        },
        fileChange(event) {
            this.noFiles = !event.target.files.length;
        },
        downloadAttachment (fileName) {
            backend.post(this.downloadUrl, {
                file_name: fileName
            }).then(response => {
                console.log(response)
                window.open(response.data.signed_url, '_blank')
            })
        },
        uploadFile() {
            this.status = 'uploading'
            this.$emit('uploading')
            const formData = new FormData()
            let attachment = this.$refs.attachmentInputRef.files[0]
            
            let sizeInBytes = attachment.size
            let sizeInMb = sizeInBytes / (1024 * 1024)

            if (sizeInMb > 60) {
                alert('File is too large. Please upload a file smaller than 50MB')
                this.status = ''
                return
            }

            formData.append('file', attachment)
            formData.append('name', this.attachmentName)
            backend.postFile(this.uploadUrl, formData).then(response => {
                this.$emit('uploaded', response.data)
                console.log(response)
                this.status = 'loaded'
                this.$emit('closed')
            })
        }, 
    }
}

</script>
