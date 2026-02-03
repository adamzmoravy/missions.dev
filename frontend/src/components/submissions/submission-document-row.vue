""<template>

    <div>
             
        <ui-document-frame @clicked="showFurtherInfoDocument = true" :document-name="$trans('Further info')" >

            <div v-if="submission.custom_language.long_description" class="scaled-down-document medium user-edited" v-html="submission.custom_language.long_description.html">
            </div>
        </ui-document-frame>

        <ui-modal size="tall" :open="showFurtherInfoDocument" @close="showFurtherInfoDocument = false" :title="$trans('Further info')">
            <template #body>

                <div class="user-edited">

                    <div v-if="!editFurtherInfoDocument" class="user-edited px-3 pt-2" v-html="submission.custom_language.long_description.html">
                    </div>

                    <div class="h-100" v-if="editFurtherInfoDocument">
                        <quill-editor  
                        class="user-edited medium editor"
                        ref="quillEditorRef" 
                        :content="submission.custom_language.long_description.value"
                        :options="quillEditorConfig"
                        @change="onQuillEditorChange($event)" />
                    </div>

                </div>


            </template>

            <template #footer v-if="isUserEditor">
                <div class="modal-buttons">
                    <ui-button :label="$trans('Edit')" class="mr-3 d-none" button-type="new" v-if="!editFurtherInfoDocument" @click="editFurtherInfoDocument = true"></ui-button>
                    <ui-button :label="$trans('Close')" v-if="!editFurtherInfoDocument" @click="showFurtherInfoDocument = flase"></ui-button>

                    <ui-button :label="$trans('Save')" button-type="action" class="mr-3" v-if="editFurtherInfoDocument" @click="editFurtherInfoDocument = false"></ui-button>
                    <ui-button :label="$trans('Cancel')" button-type="remove"  v-if="editFurtherInfoDocument" @click="editFurtherInfoDocument = false"></ui-button>
                </div>
            </template>
        </ui-modal>
    </div>

</template>

<style scoped>

.scaled-down-document {
    padding:2rem;
    width:616px;
    transform:scale(0.2);
    aspect-ratio: 1 / 1.41;
    transform-origin: top left;
}

.editor {
    height: calc(100% - 3rem);
}

</style>

<script>

export default {
    name: 'submission-document-row',
    data() {
        return {
            showFurtherInfoDocument: false,
            editFurtherInfoDocument: false,
            quillEditorConfig: {
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'], // toggled buttons
                            ['blockquote'],
                            [{
                                'list': 'ordered'
                            }, {
                                'list': 'bullet'
                            }],
                            [{
                                'header': [1, 2, false]
                            }],
                            ['clean'], // remove formatting button
                            ['link', 'image', 'video']
                        ]
                    }
                }
        }
    },
    props: {
        isUserEditor: {
            required: false,
            default: false,
            type: Boolean
        },
        submission: {
            required: true,
            type: Object
        }
    }
}

</script>