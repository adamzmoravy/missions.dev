<template>

    <div>

        <ui-button :size="'md'" @click="showAddSubmission = true" :button-type="'new'" :label="'+ ' + $trans('Add new')" ></ui-button>


            <single-modal :open="showAddSubmission" @closed="showAddSubmission = false">
                <template v-slot:modal-content>
                    <single-modal-inner @closed="$emit('closed')">
                        <template v-slot:header>
                                <p class="large" v-if="submissionType==5">New issue</p>
                                <p class="large" v-if="submissionType==6">New knowledge</p>
                            </template>
                            <template v-slot:subheader>
                            </template>
                            <template v-slot:body>


                                <div v-if="!submissionId" class="row w-100">

                                    <div class="col-md-10 col-12" v-if="submissionType==5">
                                        <p v-if="explainer" v-html="explainer"></p>
                                        <h2 class="medium-2 mt-2">What seems to be the matter?</h2>
                                        <input v-model="submissionTitle" type="text" class="form-control form-lg">

                                        <h2 class="medium-2 mt-4">What do we need to know to resolve this?</h2>
                                        <textarea v-model="submissionShortDescription" class="form-control form-lg" rows="8" ></textarea>

                                    </div>
                                    <div class="col-md-10 col-12" v-if="submissionType==6">

                                        <h2 class="medium-2 mt-2">Title</h2>
                                        <input v-model="submissionTitle" type="text" class="form-control form-lg">

                                        <h2 class="medium-2 mt-4">What would be good for people to know?</h2>
                                        <textarea v-model="submissionShortDescription" class="form-control form-lg" rows="8" ></textarea>

                                    </div>

                                    <div class="col-md-10 col-12" v-if="submissionType==99">

                                    <h2 class="medium-2 mt-2">Title</h2>
                                    <input v-model="submissionTitle" type="text" class="form-control form-lg">

                                    <h2 class="medium-2 mt-4">Short description</h2>
                                    <textarea v-model="submissionShortDescription" class="form-control form-lg" rows="8" ></textarea>

                                    </div>


                                </div>

                                <div v-if="submissionId" class="row w-100">
                                    <experiment-page inline-template :experiment-id="submissionId">
                                        <div>
                                            <img v-if="experiment" :src="getExperimentPicUrl('/img/placeholder-iconic.png', experiment, 'top_image', 400, 1000, 10)" class="w-75" style="aspect-ratio:1000/400;">
                                            <h2 class="medium-2 mt-4">Would you like to upload a picture with this?</h2>

                                            <image-upload inline-template @set-image="setImage"
                                                    @upload-image="updateExperimentImage"
                                                    :show-editor-prop="true">
                                                    <input ref="imageFile"
                                                        type="file"
                                                        class="mt-4"
                                                        name="image"
                                                        v-validate.initial
                                                        data-vv-rules="image|size:10000"
                                                        accept="image/gif, image/jpeg, image/jpg, image/png"
                                                        @change="verifyAndUploadTopImage">
                                            </image-upload>

                                        </div>

                                    </experiment-page>
                                </div>

                            </template>

                            <template v-slot:footer>
                                <ui-button :working="currentlyCreatingSubmission" v-if="!submissionId" button-type="new"  class="ml-3" :label="$trans('Create')" @click="createSubmission()"></ui-button>
                                <ui-button v-if="submissionId"  class="ml-3" :label="$trans('Finish')" @click="showAddSubmission = false"></ui-button>

                            </template>
                    </single-modal-inner>
                </template>
            </single-modal>

            </div>



</template>

<script>

import backend from '../../backend.js'
export default {
    name: 'submission-wizard',
    data () {
        return {
            currentlyCreatingSubmission: false,
            submissionId: null,
            showAddSubmission: false,
            submissionTitle: '',
            submissionShortDescription: ''
        }
    },
    props: {
        explainer: {
            required: false,
            default: null
        },
        language: {
            default: function () {
                return this.$lang
            }
        },
        submissionType: {
            required: false,
            default: '3'
        }
    },
    methods: {
        createSubmission: function () {
            this.currentlyCreatingSubmission = true
            backend.post('/experiments/', {
                stage: this.submissionType
            }).then(response => {

                var submissionId = response.data.experiment.experiment_id
                let experimentLanguageBody = {}
                experimentLanguageBody.language = {
                                title: {
                                    value: this.submissionTitle
                                },
                                short_description: {
                                    value: this.submissionShortDescription
                                },
                                owner_name: {
                                    value: ''
                                }
                            }
                backend.patch('/experiments/' + submissionId + '/language/' + this.language + '/', experimentLanguageBody)
                .then(response => {
                    backend.post('/experiments/' + submissionId + '/publish/', {
                    }).then(response => {
                        console.log('published')
                    })
                    console.log(response.data)
                    this.currentlyCreatingSubmission = false
                    this.submissionId = submissionId         
                })

            })
        }
    }

}

</script>