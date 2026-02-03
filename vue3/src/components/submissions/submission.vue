<template>

    <div>
        <div v-if="accessible == false">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <h1 class="mt-lg-5 mt-4">{{$trans('Private')}}</h1>
                        <h2>{{$trans('not_accessible')}}.</h2>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="responseStatus.getExperiment == 'not_found' || submissionRemoved">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <h1 class="mt-lg-5 mt-4 x-large">Nothing here</h1>
                        <h2 class="large">This submission has not been found. It may have been removed.</h2>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="accessible && responseStatus.getExperiment != 'not_found' && !submissionRemoved">
            <div v-if="submission" :class="'stage-' + $ui.stageHandles[submission.stage]">


                <ui-page-body v-if="submission" class="px-0 px-sm-3">

                    <div class="container">
                        <div class="row">
                            <div class="col-md-7 px-0 px-sm-3 col-xlg-7">

                                    <ui-panel class="bg-white mb-3 px-4 submission-panel" :class="submission.status">
                                        <div class="top-space">

                                            <div class="location-label">
                                                <span class="d-flex small align-items-center float-right" v-if="submission.visibility && submission.visibility == 'private'"><img style="height:1em;margin-left:5px;margin-right:2px;" :src="'/img/private.svg'">{{ submission.visibility }}</span>

                                                <span class="d-flex small align-items-center float-right" v-if="submission.geographic_location && submission.geographic_location.vicinity" v-tooltip="submission.geographic_location.formatted_address"><img style="height:1.2em;" :src="'/img/location.svg'">{{$truncate(submission.geographic_location.vicinity, 18) }}</span>
                                            </div>

                                        </div>
                                        <div v-if="isUserEditor" class="d-flex°">
                                            <ui-button :size="'sm'" class="mb-2" @click="ui.editModal = true">Edit</ui-button>
                                            <ui-button :size="'sm'" button-type="new" class="mb-2 ml-2" @click="ui.shareModal = true" v-if="$ui.submissionFeatures.includes('share') && submission.visibility != 'private'">Share</ui-button>

                                            <template v-if="submission.stage == 5 && isUserAdmin">
                                                <ui-button v-if="submission.status != 'resolved'" :size="'sm'" button-type="action" class="mb-2 ml-2" @click="setStatus('resolved')">Resolve</ui-button>
                                                <ui-button v-else :size="'sm'" button-type="new" class="mb-2 ml-2" @click="setStatus('published')">Reopen</ui-button>
                                            </template>

                                            <ui-modal :title="'Edit submission'" :size="'tall'" :open="ui.editModal" @close="ui.editModal = false">
                                                <template #body>
                                                    <submission-edit @removed="submissionHasBeenRemoved" class="w-100" @saved="setup()" :submission="submission"></submission-edit>                                                    
                                                </template>
                                            </ui-modal>

                                            <submission-share @close="ui.shareModal = false" v-if="ui.shareModal" class="w-100" @saved="setup()" :submission="submission"></submission-share>

                                        </div>

                                        <ui-img-dynamic :height-ratio="0.4625" class="experiment-image" v-if="submission.image_collection.top_image" :src="submission.image_collection.top_image.filename" />

                                        <h2 class="large-2 mt-4 submission-title">
                                            {{submission.language.title.value}}
                                        </h2>
                                        <p class="small-2 mt-2 mb-2" :title="submission.created_at"><strong><timeago :datetime="submission.created_at" :auto-update="60"></timeago></strong></p>


                                        <ui-read-more more-str="See more" class="medium mb-4" style="word-wrap:break-word;" id="short-description" :text="submission.language.short_description.html" link="#" less-str="See less" :tolerance="300" :max-chars="shortDescriptionMaxChars"></ui-read-more>

                                        <ui-tags :tags="submission.tags" v-if="submission.tags.length > 0"></ui-tags>
                                        
                                        <hr class="mb-3 mt-0 d-none">

                                        <submission-participate
                                            v-if="showParticipate"
                                            class="pb-0"
                                            :submission-interactor="submissionInteractor"
                                            :experiment="submission"
                                            :my-relationships="[]"
                                            @participation-updated="fetchSubmission"
                                        />

                                    </ui-panel>

                                    <ui-panel class="bg-white mb-3" v-if="isUserEditor">

                                        <h3 class="medium mb-3" v-if="false">{{$trans('Add new:')}}</h3>
                                        <ui-button @click="ui.addAttachment=true" size="sm">+ {{ $trans('Document') }}</ui-button>
                                        <ui-button @click="ui.uploadImageModal=true" class="ml-2" size="sm" button-type="new">+ {{ $trans('Image') }}</ui-button>

                                        <!--
                                        <ui-button @click="ui.addEvent=true" class="ml-2" size="sm" button-type="cancel">+ {{ $trans('Event') }}</ui-button>

                                        <group-event-new @event-added="$stores.groups.fetchCalendarEvents()" @close="ui.addEvent = null" :post-event-url="'/experiments/' + submissionId + '/calendar/events/'" :open="ui.addEvent" />
                                        -->
                                    </ui-panel>

                                    <ui-panel class="mb-3 bg-white" v-if="submission.attachments.length > 0 || ui.addAttachment">
                                        <h3 class="medium mb-3">{{$trans('Documents')}}</h3>

                                        <attachments
                                            @closed="ui.addAttachment=false"
                                            :add-attachment="ui.addAttachment"
                                            :can-upload="isUserEditor"
                                            :attachments="submission.attachments"
                                            :remove-url="'/experiments/' + submissionId + '/attachments/'"
                                            :download-url="'/experiments/' + submissionId + '/attachments/file_request/'"
                                            :upload-url="'/experiments/' + submissionId + '/attachments/file/'"
                                        >

                                        </attachments>

                                    </ui-panel>

                                    <ui-panel class="bg-white mb-3" v-if="photoStream.length > 0 || ui.uploadImageModal">
                                        <h3 class="medium mb-3">Images</h3>
                                        <image-stream
                                            @close-upload-dialogue="ui.uploadImageModal=false"
                                            :offer-add-image="false"
                                            ref="imageStreamRef"
                                            :upload-image-modal="ui.uploadImageModal"
                                            @new-image="fetchPhotoStream"
                                            :post-image-url="'/experiments/' + this.submissionId + '/photo_stream/'"
                                            class="mt-2 mb-2"
                                            :images="photoStream"
                                        >
            
                                        </image-stream>
                                    </ui-panel> 


                                    <group-events-show v-slot="slotProps" class="pl-0" :calendars="['submission-' + submissionId]" :calendar-events="$stores.groups.calendarEvents">                        

                                        <vue-horizontal>
                                            <ui-panel v-for="event in slotProps.events" class="bg-white w-100 mb-3 position-relative" :key="event.event_id">
                                                <div class="when-is-it">
                                                    <span v-if="!event.started">Starts <timeago :datetime="event.start" :auto-update="60" /></span>
                                                    <span v-else-if="event.started && !event.ended">Started <timeago :datetime="event.start" :auto-update="60" /></span> 
                                                    <span v-else-if="event.ended">Ended <timeago :datetime="event.end" :auto-update="60" /></span> 
                                                </div>
                                                <div class="mb-2">
                                                    <a @click="slotProps.show(event.event_id)" class="large stretched-link">{{event.name}}</a>
                                                    <ui-img-dynamic class="mt-2" v-if="event.image" :img-style="{width: '100%'}" :heightRatio="0.5625" :src="event.image"/>
                                                </div> 
                                            </ui-panel>
                                        </vue-horizontal> 
                                    </group-events-show>

                                <div class="mb-4" v-if="submission.custom_language.long_description">
                                    <h2 class="medium mb-3">{{$trans('Documents')}}</h2>


                                    <submission-document-row :is-user-editor="isUserEditor" :submission="submission">

                                    </submission-document-row>
                                </div>


                                
                        
                                <comment-topic class="comments ml-0 pr-0"
                                    :post-format="'tiptap-json'"
                                    :topic="submission.uuid"
                                    :fetch-comments-url="'/experiments/' + submissionId + '/topic/'"
                                    :post-reply-url="'/experiments/' + submissionId + '/comment/'"
                                    :post-comment-url="'/experiments/' + submissionId + '/topic/'"
                                    :offer-hats="$ui.offerHatsForSubmissionStages.includes(submission.stage)"
                                    :post-explainer="$trans('submission_post_explainer')"
                                    :experiment-id="submissionId"
                                    :offer-cancel="false"                                    
                                    />

                     

                            </div>
                            <div class="col-md-4 pt-3 pl-4">

                                <submission-related-submissions-side :is-user-editor="isUserEditor" @submission-updated="fetchSubmission" :submission="submission" :submission-interactor="submissionInteractor"></submission-related-submissions-side>
                                <submission-details-people :submissionInteractor="submissionInteractor" :is-user-editor="isUserEditor" class="mt-0" :label="$trans(peopleLabel)" :team-ids="teamIds"></submission-details-people>
                                
                            </div>

                        </div>

                    </div>
                </ui-page-body>

            </div>
        </div>
    </div>

</template>

<style scoped>
.resolved .submission-title {
    color: #6c757d;
    text-decoration: line-through;
}

.submission-panel {
    padding-top:5rem;
}
.multiline-ellipsis {
    overflow: hidden;
   display: -webkit-box;
   -webkit-box-orient: vertical;
}

#short-description p {
    font-size: 20px!important;
}
.short-description p {
    font-size: 20px!important;
}


</style>

<script>
import { useProfilesStore } from '../../stores/profiles'
import { useUIStore } from '../../stores/ui'
import { useAuthStore } from '../../stores/auth'
import { mapStores } from 'pinia'
import { useSubmission } from './submission.js'
import backend from '../../backend.js'

import SubmissionEdit from './submission-edit.vue'
import SubmissionParticipate from './submission-participate.vue'
import attachments from '../attachments.vue'
import SubmissionDocumentRow from './submission-document-row.vue'
import commentTopic from '../comments/comment-topic.vue'
import SubmissionRelatedSubmissionsSide from './submission-related-submissions-side.vue'
import SubmissionDetailsPeople from './submission-details-people.vue'
import groupEventNew from '../groups/group-event-new.vue'
import groupEventsShow from '../groups/group-events-show.vue'
import SubmissionShare from './submission-share.vue'

import imageStream from '../image-stream.vue'

var submission = useSubmission()

export default {

    name: 'submission',
    head() {
        return {
            title: this.submissionTitle,
        }
    },
    data: function () {
        return {
            imageTransform: {
                scale: 1,
                offsetX: 0,
                offsetY: 0,
            },
            photoStream: [],
            tiptapContent: null,
            ui: {
                uploadImageModal: false,
                addAttachment: false,
                addEvent: false,
                editModal: false,
                shareModal: false,
                showWholesShortDescription: false
            },
            submissionRemoved: false,
            isMounted: false,
            responseStatus: {
                getExperiment: null
            }
        }
    },
    components: {
        imageStream,
        SubmissionEdit,
        SubmissionParticipate,
        attachments,
        SubmissionDocumentRow,
        commentTopic,
        SubmissionRelatedSubmissionsSide,
        SubmissionDetailsPeople,
        groupEventNew,
        groupEventsShow,
        SubmissionShare
    },
    watch: {
        latestSubmissionUpdatedEvent: function (newVal) {
            this.fetchSubmission()
        }
    },
    computed: {
        showParticipate: function () {
            return this.submission.stage != 8
        },
        peopleLabel: function () {
            if (this.submission.stage == 8) {
                return 'Organisation team'

            }
            return 'Collaborators'
        },
        submissionTitle: function () {
            if (this.submission != null) {
                return this.submission.language.title.value + ' | ' + this.$config.platform.site_title
            }
            return this.$config.platform.site_title
        },
        latestSubmissionUpdatedEvent: function () {
            return this.$stores.events.getLatestEventByName('experiment_updated')
        },
        topImage: function () {
            if (submission.topImage.value !== null) {
                return submission.topImage.value
            }
            return null
        },
        shortDescriptionMaxChars: function () {
            let chars = 500
            if (this.topImage !== null) {
                chars = 300
            }
            return chars
        },
        submission: function () {
            return submission.data.value
        },
        submissionInteractor: function () {
            return submission
        },
        ...mapStores(useProfilesStore, useUIStore, useAuthStore),
        currentUser: function () {
            return this.authStore.userData
        },
        isUserAdmin: function () {
            if (!this.authStore.isAuthenticated) {
                return false
            }
            return this.currentUser.user_type == 'admin'
        },
        isUserEditor: function () {

            if (!this.authStore.isAuthenticated) {
                return false
            }

            if (this.currentUser.user_type == 'admin') {
                return true
            }
            if (this.currentUser.user_id && this.teamIds.includes(this.currentUser.user_id.toString()))
            {
                return true
            }

            return false
        },
        accessible: function () {
            return true
            if (this.submission) {
                return this.submission.accessible
            }
            return false
        },
        teamIds: function () {
            if (this.submission) {
                return this.submission.team_ids
            }
            return []
        },
        experimentId: function () {
            if (this.submission) {
                return this.submission.experiment_id
            }
            return null
        }
    },
    methods: {
        setStatus: function (status) {
            backend.patch('/experiments/' + this.submissionId + '/settings/', {
                settings: {
                    status: status
                }
            }).then((response) => {
                this.fetchSubmission()
            })
        },
        addNewImage: function () {
            this.$refs.imageStreamRef.openUploadDialogue()
        },
        fetchPhotoStream: function () {
                backend.get('/experiments/' + this.submissionId + '/photo_stream/50/').then((response) => {
                    this.photoStream = response.data.photo_stream
                }, (error) => {
                    this.registerNotification({
                        message: error.response.data.message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
        },        
        submissionHasBeenRemoved: function () {
            this.submissionRemoved = true
        },
        changeTitle: function (title) {
            console.log(title)
            submission.editor.changeTitle(title)
        },
        setup: function() {
            this.$stores.groups.fetchCalendarEvents()
            this.fetchSubmission()
            this.fetchPhotoStream()
        },
        fetchSubmission: function () {
            submission.fetch(this.submissionId, this.$lang)
        }
    },
    props: {
        submissionId: {
            required: true
        }
    },
    mounted: function () {
        this.isMounted = true;
    },
    created: function () {
        this.setup()

    },
    beforeDestroy: function () {
        submission.data.value = null
    }

}

</script>