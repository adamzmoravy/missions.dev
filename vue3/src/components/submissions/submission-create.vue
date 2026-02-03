<template>
    
    <ui-modal :open="mode=='select-type'" :title="$trans('Create new submission')" :sub-title=" $trans('new_submission_question')" size="tall" @close="close()">
            <template #body>
                <div class="row">
                    <div class="col-md-12">

                        <div v-for="stage in submissionStages" class="d-md-flex align-items-center position-relative mb-4 mb-md-0">
                            <div class="d-flex align-items-center my-md-3 my-2">
                                <stage-icon :stage="stage"></stage-icon>
                                <div class="capitalize ml-4 mr-2 stage-name-link"><ui-a class="stretched-link" @click="mode='create-new';submissionType=stage;">{{ $trans(submissionStageHandles[stage]) }}</ui-a></div>
                            </div>
                            <div>{{$trans(submissionStageHandles[stage] + '_explanation')}}</div>
                        </div>
                    </div>
                </div>
            </template>
    </ui-modal>

    <ui-modal :open="mode=='create-new'" :title="createSubmissionTypeTitle" size="tall" @close="close()">

            <template #body>
                <div class="col-md-10 col-12" >
                    <p v-if="explainer" v-html="explainer"></p>

                    <h2 class="medium-2 mt-2">{{ submissionTitleLabel }}</h2>
                    <input v-model="submissionTitle" type="text" class="form-control form-lg">

                    <h2 class="medium-2 mt-4"> {{ submissionShortDescriptionTitle }}</h2>
                    <textarea v-model="submissionShortDescription" class="form-control form-lg" rows="6" ></textarea>

                    <template v-if="$ui.offerACLForSubmissionStages.includes(submissionType)">
                        <h2 class="medium-2 mt-4"> {{ $trans('Who can see this ') + $trans(this.submissionStageHandles[this.submissionType]) }}?</h2>
                        <div class="form-check">
                            <input v-model="submissionVisibility" class="form-check-input" type="radio" name="visibility" id="visibilityPublic" value="public">
                            <label class="form-check-label" for="visibilityPublic">
                                <strong>{{ $trans('Public') }}</strong> - {{ $trans('Anyone can see this.') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input v-model="submissionVisibility" class="form-check-input" type="radio" name="visibility" id="visibilityPrivate" value="private">
                            <label class="form-check-label" for="visibilityPrivate">
                                <strong>{{ $trans('Private') }}</strong> - {{ $trans('Only you, collaborators and admins.') }}
                            </label>
                        </div>

                    </template>
                    


                </div>

            </template>
            <template #footer>
                <div class="d-flex justify-content-center">
                    <ui-button :disabled="!isReadyToSubmit" :working="creatingSubmissionBackend" @click="createSubmission" class="mt-4" size="lg" button-type="action">{{ $trans('Create') }}</ui-button>
                </div>
            </template>

    </ui-modal>


    <ui-steps-modal :title="editSubmissionTitle"  @close="close" v-if="mode=='edit-submission'"  :stepsStyle="'yellow'" @finished="mode='finished'" class="steps" :stages="stagesComputed" @stage-changed="stage = $event">

        <template #top="slotProps">

            <div v-if="slotProps.stageName == 'related'" class="mb-3 mt-2 mx-3">

                <h2 class="medium-2 mt-0 mb-3">{{ $trans('add_related') }}</h2>

                <ui-input-text v-if="allSubmissions.length > 8" v-model="searchRelated" :placeholder="$trans('Search')" :autofocus="true" />

            </div>


            <div v-if="slotProps.stageName == 'tags'" class="mb-3 mt-2 mx-3">

                <h2 class="medium-2 mt-0 mb-3">{{ $trans('Please select relevant tags') }}</h2>

            </div>


            <div v-if="slotProps.stageName == 'team'" class="mb-3 mt-2 mx-3">


                <h2 class="medium-2 mt-0 mb-2">{{ $trans('Add a team member') }}</h2>
                <p class="medium" v-if="submission.visibility == 'public'">{{ $trans('added_team_members_can_edit') }}</p>
                <p class="medium" v-else>{{ $trans('added_team_members_can_edit_view') }}</p>

                <ui-input-text v-if="allSubmissions.length > 8" v-model="searchTeam" :placeholder="$trans('Search')" :autofocus="true" />

            </div>


        </template>
        <template #body="slotProps">

            <div v-if="slotProps.stageName == 'image'" class="steps-inside w-100">

                <h2 class="medium-2 mt-4">Would you like to upload a picture with this?</h2>
                <ui-img-dynamic :height-ratio="0.4625" class="experiment-image" v-if="submission.image_collection.top_image" :src="submission.image_collection.top_image.url" />
                <img v-else src="../img/placeholder-iconic.png" class="clickable" style="aspect-ratio: 2.16;width:80%;" @click="uploadImage()">
                <ui-image-upload class="mt-3" :button-label="$trans('Upload')" @uploaded="saveImage" ref="image-upload" />

            </div>

            <div v-if="slotProps.stageName == 'tags'" class="tags-container flex-fill d-flex flex-column">

                    <div class="related-container flex-fill d-flex flex-column">
                       <ui-tags :tags="relevantTags" :selected-tags="submission.tags" @deselected="removeTag" @selected="onTagSelected" />
                    </div>
            </div>

            <div v-if="slotProps.stageName == 'related'" class="stage-panel flex-fill">

                <div class="related-container">

                    <entities 
                        class="flex-fill d-flex mr-1"
                        :multiSelect="true"
                        @selected="addRelatedSubmission" 
                        @unselected="removeRelatedSubmission" 
                        :multi-select-selected="submission.related_experiments" 
                        :includeLink="false" 
                        :entities="allSubmissions"  
                        :search-prop="searchRelated"
                        />
     
                    </div>

                </div>
                <div v-if="slotProps.stageName == 'team'" class="stage-panel flex-fill">

                    <div class="related-container">

                        <entities 
                            class="flex-fill d-flex mr-1"
                            :multiSelect="true"
                            @selected="addTeam" 
                            @unselected="removeTeam" 
                            :multi-select-selected="submission.team_ids" 
                            :includeLink="false" 
                            :entities="allProfiles"  
                            :search-prop="searchTeam"
                            />

                        </div>

                </div>

        </template>

    </ui-steps-modal>

    <ui-modal :open="mode=='finished'" size="tall" :title="$trans('Submission ready')" @close="close()">
        <template #body>

            <div v-if="mode == 'finished'" class="h-100">
                <div class="d-flex align-items-center">
                    <div>
                    <div class="mt-2 medium-2">{{$trans('Your submission is ready.')}}</div>
                    <div class="mt-2 medium" v-if="submission.visibility == 'private'">{{$trans(' It is currently private and only visible to you, your collaborators and admins.')}}</div>
                    <div class="mt-2 medium" v-if="submission.visibility == 'public' && $ui.submissionFeatures.includes('share')">{{$trans(' You can now continue to edit the submission, or share it in an email with select members of the community.')}}</div>
                    <div class="mt-2 medium" v-else>{{$trans('Head over to the')}} <ui-a :href="'/submission/' + submissionPreview.experiment_id">{{ $trans('submission page') }}</ui-a> {{$trans('and add more details to it or close this windows and do it later.')}}</div>
                    </div>

                </div>
                <div class="row mt-3 justify-content-center ">
                    <submission-preview class="col-md-6" :experiment="submissionPreview"  />
                </div>

            </div>
        </template>
        <template #footer>
    
                <div class="d-flex justify-content-center mt-5">

                    <ui-button :href="'/submission/' +  submissionId" @click="close()">{{ $trans('Edit') }}</ui-button>
                    <ui-button v-if="submission.visibility == 'public' && $ui.submissionFeatures.includes('share')" @click="mode='share'" class="ml-2" button-type="new">{{ $trans('Share') }}</ui-button>

                </div>

        </template>
    </ui-modal>
        <submission-share v-if="mode=='share'" :submission="submissionPreview" @close="close()" @saved="mode='finished'" />

</template>

<style scoped>
.experiment-image {
    width:80%!important;
}

.related-container {
}
.select-submissions {
    max-height:100%;
}

.steps {
    width:100%;
    height:100%;
}

.steps-inside {
}

.stage-name-link {
    font-weight:900;
}

.stage-panel {
}
.select-team-members {

}

</style>

<script>
import backend from '../../backend.js'

import stageIcon from './stage-icon.vue'

import { useSubmission } from './submission.js'

import SubmissionShare from './submission-share.vue'
var submission = useSubmission()

export default {
    name: 'submission-create',
    data: function () {
        return {
            submissionVisibility: 'public',
            searchRelated: '',
            searchTeam: '',
            submissionType: null,
            mode: 'select-type',
            submissionId: null,
            submissionTitle: '',
            submissionShortDescription: '',
            creatingSubmissionBackend: false,
            published: false
        }
    },
    watch: {
        mode: function (newValue) {
            if (newValue == 'finished') {
                this.publishSubmission()
            }
        }
    },
    computed: {
        textHasBeenEntered: function () {
            return this.submissionTitle != '' || this.submissionShortDescription != ''
        },
        stagesComputed: function () {
            let stages = this.stages

            if (this.relevantTags.length == 0) {
                stages = stages.filter(stage => stage.name != 'tags')
            }

            return stages
        },
        relevantTags: function () {
            return this.$stores.tags.getTagsByTypes(['submissions-type-' + this.submissionType])
        },
        submissionTitleLabel: function () {

            if (this.submissionType == 5) {
                return this.$trans('What seems to be the matter?');
            }

            return this.$trans('Title') + ' of ' + this.$trans(this.submissionStageHandles[this.submissionType])
        },
        submissionShortDescriptionTitle: function () {

            if (this.submissionType == 5) {
                return this.$trans('What do we need to know to resolve this together?');
            }


            if (this.submissionType == 6) {
                return this.$trans('What would be good for people to know?');
            }

            return this.$trans('Short description')
        },
        createSubmissionTypeTitle: function () {
            return this.$trans('Create new') + ' ' + this.$trans(this.submissionStageHandles[this.submissionType])
        },
        editSubmissionTitle: function () {
            return this.$trans('Edit') + ' ' + this.$trans(this.submissionStageHandles[this.submissionType])
        },
        explainer: function () {

            if (this.submissionType == 5) {
                return this.$trans('Before submitting, please check if someone alrady posted about this, or if there is something in the <a href=/guide/>guide</a> about this.')
            }

            return null
        }, 
        language: function () {
            return this.$lang
        },
        submissionStageHandles: function () {
            return this.$ui.stageHandles
        },        
        submissionStages: function () {
            return this.$ui.ideationStages
        },
        submissionInteractor: function () {
            return submission
        },
        submission: function () {
            return submission.data.value
        },
        allSubmissions: function () {
            let entities =  this.$stores.submissions.getEntities([
                {
                    sorter: 'updated',
                    direction: 'asc'
                }
            ])
            return entities.filter(entity => entity.id != this.submissionId)

        },
        allProfiles: function () {
            let entities =  this.$stores.profiles.getEntities([
           
            ])
            return entities.filter(entity => entity.id != String(this.$currentUser.user_id))

        },
        relatedSubmissionsEntities: function () {
            return this.$stores.submissions.getEntities([], [
                {
                    selection: 'ids',
                    value: this.submission.related_experiments
                }
            ])

        },
        isReadyToSubmit: function () {
            return this.submissionTitle != '' && this.submissionShortDescription != ''
        },
        submissionPreview: function () {
            if (this.submissionId == null) {
                return null
            }
            return this.$stores.submissions.getSubmissionPreview(this.submissionId)
        }
    },
    components: {
        stageIcon,
        SubmissionShare
    },
    methods: {
        confirmClose: function () {
            if (this.textHasBeenEntered && this.submissionId == null) {

                if (!confirm('You have unsaved changes, are you sure you want to leave?')) {

                    return false

                } 
                return true

            } else {
                this.publishSubmission()
                return true
            }
        },
        publishSubmission: function () {
            if (this.published || this.submissionId == null) {
                return
            }
            backend.post('/experiments/' + this.submissionId + '/publish/', {
                }).then(response => {
                    this.published = true
                    this.$stores.ui.notifySuccess(this.$trans('Submission published'))
                    return true
                })
        },
        onTagSelected: function (tagId) {
            backend.post('/experiments/' + this.submissionId + '/tag/', {
                tag: tagId
            }).then(response => {
                submission.fetch(this.submissionId, this.language)
            })

        },
        removeTag: function (tagId) {
            backend.delete('/experiments/' + this.submissionId + '/tag/' + tagId + '/').then(response => {
                submission.fetch(this.submissionId, this.language)
            })
        },
        addRelatedSubmission: function (submissionEntity) {
            this.submissionInteractor.addRelatedSubmission(submissionEntity.experiment_id)
        },
        removeRelatedSubmission: function (submissionEntity) {
            this.submissionInteractor.removeRelatedSubmission(submissionEntity.experiment_id)
        },
        addTeam: function (profileEntity) {
            this.submissionInteractor.addToTeam(profileEntity.user_id)
        },
        removeTeam: function (profileEntity) {
            this.submissionInteractor.removeFromTeam(profileEntity.user_id)
        },
        close: function () {
            if (this.confirmClose()) {
                this.$stores.ui.newSubmission = null
                this.$emit('close')
                this.mode = 'select-type'
                this.submissionId = null
                this.submissionTitle = ''
                this.submissionShortDescription = ''
            }


        },
        uploadImage: function () {
            this.$refs['image-upload'].upload()
        },
        saveImage: function (data) {
            submission.saveTopImage(data.image.filename).then(response => {
                submission.fetch(this.submissionId, this.language)
            })
        },
        createSubmission: function () {
            this.creatingSubmissionBackend = true
            backend.post('/experiments/', {
                stage: this.submissionType,
                visibility: this.submissionVisibility
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
//                    this.publishSubmission()
                    this.creatingSubmissionBackend = false
                    this.submissionId = submissionId  
                    submission.fetch(this.submissionId, this.$lang).then(response => {
                       this.creatingSubmissionBackend = false
                       this.mode = 'edit-submission'
                       this.$emit('submission-created', submissionId)
                    })

                })

            })
        }

    },
    props: {
        stages: {
            required: false,
            default: function () {
                return [
                    {
                        'name': 'image'
                    },
                    {
                        'name': 'tags',
                    },
                    {
                        'name': 'related',
                    },
                    {
                        'name': 'team',
                    }
                ]
            }
        }
    },
    mounted: function () {
        if (this.$stores.ui.newSubmission != null) {
            if (this.$stores.ui.newSubmission.stage) {
                this.submissionType = this.$stores.ui.newSubmission.stage
                this.mode = 'create-new'
            }
        }
    },
    beforeUnmount: function () {

    }
}

</script>