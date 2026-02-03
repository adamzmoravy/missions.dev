<template>

    <div>
        <div class="mb-4">
            <h2 class="large">{{$trans('Intro')}}</h2>
            <ui-panel class="bg-white">
                <submission-intro-translations @saved="saved" :experiment-id="submission.experiment_id" :languages="$config.platform.site_languages" :translations-data="submission.translations"></submission-intro-translations>
            </ui-panel>
        </div>

        <div  class="mb-4">
            <h2 class="large">{{$trans('Image')}}</h2>
            <ui-panel class="bg-white">
                <ui-img-dynamic :mode="imageMode" :height-ratio="0.4625" class="experiment-image mb-3" v-if="submission.image_collection.top_image" :src="submission.image_collection.top_image.url"/>

                <template v-if="imageMode == 'view'">
                    <ui-image-upload class="d-inline mr-1" size="sm" :button-label="$trans('Upload')" @uploaded="saveImage"/>

                    <!--<ui-button v-if="submission.image_collection.top_image" @click="imageMode = 'adjust'" size="sm">{{$trans('Adjust')}}</ui-button>-->
                </template>
                <template v-else>
                    <ui-button @click="imageMode = 'view'" button-type="new" size="sm" class="mb-2">{{$trans('Save')}}</ui-button>
                    <ui-button @click="imageMode = 'view'" button-type="cancel" size="sm" class="mb-2 ml-1">{{$trans('Cancel')}}</ui-button>
                </template>


            </ui-panel>
        </div>
        <div>
            <h2 class="large">{{$trans('Location')}}</h2>
            <ui-panel class="bg-white">

                <submission-location-edit :submission="submission" />


            </ui-panel>
        </div>
        <div class="mt-4">
            <h2 class="large">{{$trans('Remove this submission')}}</h2>
            <ui-panel class="bg-white">
                <ui-button button-type="cancel" @click="removeSubmission()">{{ $trans('Remove') }}</ui-button>
            </ui-panel>
        </div>

    </div>

</template>

<script>
    import backend from '../../backend.js'
    import submissionIntroTranslations from './submission-intro-translations.vue'
    import submissionLocationEdit from './submission-location-edit.vue'

    export default {
        name: 'submission-edit',
        data: () => {
            return {
                imageMode: 'view'
            }
        },
        props: {
            submission: {
                required: true
            }
        },
        components: {
            submissionIntroTranslations,
            submissionLocationEdit
        },
        methods: {
            saved: function () {
                this.$emit('saved')
            },
            removeSubmission: function () {
                if (confirm('Are you sure you want to remove this submission?')) {
                    backend.delete('/experiments/' + this.submission.experiment_id + '/').then(response => {
//                        this.$emit('saved')
                        this.$emit('removed')
                        this.$emit('close')
                    })
                }
            },
            saveImage: function (data) {
                backend.patch('/experiments/' + this.submission.experiment_id + '/image_collection/', {
                    image_collection: {
                        top_image: {
                            filename: data.image.filename
                        }
                    }
                }).then(response => {
                    this.$emit('saved')
                })
            }
        }
    }

</script>