<template>
    <div v-if="loadingStatus != 'fetching'">
        <div>       
        <div v-if="false">{{status}} {{endOfRetrievedHappening - startOfRetrievedHappening}}</div>
        <div v-if="happening.config.access == 'private' && isUserAuthenticated == false">
            <div class="container mt-4">
                <div class="row">
                    <div class="col mt-4 col-md-12">
                        <ui-panel class="bg-white w-100 p-4">
                            <h3>You need to <a @click="$stores.ui.modal = 'login-modal'"><strong>{{$trans('log in')}} </strong></a> to be able to see this group.</h3>
                        </ui-panel>

                    </div>
                </div>
            </div>
        </div>

        <div v-if="happening.config.access == 'private' && isUserAuthenticated && !currentUserIsRegistered && userType != 'admin'">
            <div class="container mt-4">
                <div class="row">
                    <div class="col mt-4 col-md-6">
                        <h3>You have not been registered to this group.</h3>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="ui.mode == 'join-happening'">
            <group-register @happening-updated="setup()" :happening="happening" :current-user-is-registered="currentUserIsRegistered" :questions="happening.config.survey" :happening-id="happeningId" />
        </div>

        <div class="container d-flex justify-content-center">
            <img :src="'/img/loading-dots.svg'" style="max-width: 100%;" v-if="loadingStatus == 'fetching'">   

        </div>

        <template v-if="ui.mode == 'show-happening' && loadingStatus != 'fetching'">

            <ui-page-header v-if="(currentUserIsRegistered) || userType == 'admin'|| happening.config.access == 'public'">

                <div>
                    <div class="pt-0 pb-0 w-100 happening-head">
                        <div>
                            <h1 class="x-large mt-2">{{happening.name}}</h1>
                            <div class="pb-2">
                                <h2 class="medium">
                                    <span v-if="happening.config.access == 'private'"><strong>Private</strong></span>
                                    <span v-if="happening.config.access == 'public'"><strong>Public</strong></span>
                                    &nbsp;•&nbsp;
                                    <span v-if="happening.config.registrations == 'open'"><strong>Open</strong></span>
                                    <span v-if="happening.config.registrations == 'closed'"><strong>Closed</strong></span>
                                    <span v-if="currentUserIsRegistered">
                                        &nbsp;•&nbsp;

                                        <a @click="leaveHappening">Leave group</a>
                                    </span>
                                </h2>                            
                            </div>
                            <div class="d-inline-block mt-1 mb-1 w-100" v-if="happening.registrations.length < 0">


                                <profile-index-row
                                    class="w-100"
                                    :visible-profiles="happening.registrations"
                                    :all-profiles="happening.registrations"
                                    :profile-size="30"
                                    :show-name="false"
                                    @show-all-profiles="showSection = 'participants'"
                                    @profiles-fit=""
                                />

<!--
                                <profile-index class="d-flex h-100 w-100 flex-wrap align-content-center"
                                            :limit="8"
                                            :user-ids="allMembersString"
                                            :profile-size="40"
                                            :show-name="false"
                                            :stack-profiles="false"
                                            :max-profiles-display="8"
                                            @show-all-profiles="showSection = 'participants'"
                                        />
-->
                            </div>

                        </div>
                        <div style="display:flex;justify-content: right;">
                            
                            <div v-if="!currentUserIsRegistered && happening.config.registrations === 'open'">
                                <div class="mt-3 pt-2">
                                    <h3 class="medium text-right mb-2">{{$trans('Group is open')}}</h3>
                                    <div class=" d-flex float-right">
                                        <ui-button :working="ui.joiningHappening" @click="joinHappening">{{$trans('Join')}}</ui-button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="currentUserIsRegistered">
                                <div class="mt-0 pt-2">
                                    <h3 class="medium mb-2 text-right d-none">You are in this group. <a class="underline" @click="leaveHappening">Leave</a></h3>
                                    <div class="d-done  float-right d-none">
                                        <ui-button @click="leaveHappening" size="small" button-type="cancel">{{$trans('Leave')}}</ui-button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="happening.config.registrations === 'applications'">
                                <div class="mt-3 pt-2 text-centerjustify-content-center">
                                    <h3 class="medium text-center mb-2">Opið fyrir umsóknir</h3>
                                    <div class=" d-flex justify-content-center">
                                        <a v-if="!isUserAuthenticated" @click="$root.eventBus.$emit('show-modal', 'register-modal', true, '/happening_apply/' + happeningId + '/')" class="btn action-important action-important-yellow mb-2 " style="width: 152px; height: 60px; font-size: 22px;">Sækja um</a>
                                        <ui-a v-if="isUserAuthenticated" :href="'/happening_apply/' + happeningId + '/'" class="btn action-important action-important-yellow mb-2 " style="width: 152px; height: 60px; font-size: 22px;">Sækja um</ui-a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <ui-tabs class="mt-4" v-on:update:section="showSection = $event" :tabs="sectionsSimplified" :current-selection="showSection" />


                </div>
            </ui-page-header>



            <ui-page-body>

                <div class="row">
                    <div class="col-12 col-md-8 pr-0 pl-0 pr-sm-3 pl-sm-3 pr-md-5 pl-md-3">

                        <div v-show="showSection == 'intro'">

                            <group-intro v-if="isUserAuthenticated || getCustomLanguage('intro').html" :happening-id="happeningId" @language-updated="fetchHappening()" :language-field="getCustomLanguage('intro')" :update-url="'/happenings/' + happeningId + '/custom_language/' + $lang + '/'" />

                        </div>

                        <div v-show="showSection == 'schedule'">

                            <group-events-show v-slot="slotProps" class="mr-2" :calendar-events="events" @saved="fetchEvents()" ref="showEvents" :happening-id="happeningId">
                                
                                <ui-panel v-for="event in slotProps.events" class="bg-white mb-3 position-relative" :key="event.event_id">
                                    <div class="when-is-it">
                                        <span v-if="!event.started">Starts <timeago :datetime="event.start" :auto-update="60" /></span>
                                        <span v-else-if="event.started && !event.ended">Started <timeago :datetime="event.start" :auto-update="60" /></span> 
                                        <span v-else-if="event.ended">Ended <timeago :datetime="event.end" :auto-update="60" /></span> 
                                    </div>
                                    <div class="mb-2">
                                        <a @click="slotProps.show(event.event_id)" class="medium-2 stretched-link">{{event.name}}</a>
                                        <ui-img-dynamic class="mt-2" v-if="event.image" :img-style="{width: '100%'}" :heightRatio="0.5625" :src="event.image"/>
                                    </div>
                                </ui-panel>

                            </group-events-show>

                        </div>
                        <div v-show="showSection == 'guide'">

                            Guide missing
                            
                        </div>
                        <div v-show="showSection == 'participants'">

                            <div v-if="userType=='admin'" class="d-flex mb-2">
                                <a @click="ui.manageParticipants = true" class="ml-auto edit-button-pencil"><i class="fa fa-pencil"></i> Manage participants</a>

                                <ui-modal :title="$trans('Manage participants')" :open="ui.manageParticipants" @close="ui.manageParticipants = false">

                                    <template #subheader>
                                        <div class="container">
                                            <p class="medium">Select users who should be in group. Unselect to remove them!.</p>
 
                                        </div>
                                    </template>

                                    <template #body>


                                <profile-index-row
                                    class="w-100"
                                    :visible-profiles="happening.registrations"
                                    :all-profiles="happening.registrations"
                                    :profile-size="30"
                                    :show-name="false"
                                    @show-all-profiles="showSection = 'participants'"
                                    @profiles-fit=""
                                />
-->

                                    </template>

                                </ui-modal>
                                
                            </div>
                            <div>

                                <div class="d-inline-block" v-if="!participantsString">
                                    <p>There are currently no registered participants.</p>
                                </div>
                                <div class="d-inline-block w-100" v-if="participantsString">
                                    <h3 class="">Participants</h3>
                                    <profile-index-row class="pt-2 ml-0"
                                        style="width:100%;"
                                        :limit="0"
                                    :visible-profiles="happening.registrations"
                                    :all-profiles="happening.registrations"
                                        :type="'list'"
                                        :user-ids="participantsString" 
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-show="showSection == 'config'">

                            <group-config

                                :happening="happening"
                                @update-config="configUpdated"
                            />


                        </div>
                        <div v-show="showSection == 'announcements'">
<!--
                            <Teleport :to="'comment-' + happening.uuid + '-1'">

                                <ui-panel class="bg-white mb-4" v-if="photoStream.length > 0">
                                    <h3 class="medium mb-3">Anonymous moments</h3>
                                    <image-stream
                            
                                        @new-image="fetchPhotoStream"
                                        :post-image-url="'/happenings/' + this.happeningId + '/photo_stream/'"
                                        class="mt-2 mb-2"
                                        :images="photoStream"
                                    >
        
                                    </image-stream>
                                </ui-panel>

                            </Teleport>


                            <Teleport :to="'comment-' + happening.uuid + '-2'">
                                    <group-events-show v-slot="slotProps" class="mr-2" :calendar-events="events" @saved="fetchEvents()" ref="showEvents" :happening-id="happeningId">
                                        
                                        <vue-horizontal>
                                            <ui-panel v-for="event in slotProps.events" class="bg-white w-100 mb-3 position-relative" :key="event.event_id">
                                                <div class="when-is-it">
                                                    <span v-if="!event.started">Starts <timeago :datetime="event.start" :auto-update="60" /></span>
                                                    <span v-else-if="event.started && !event.ended">Started <timeago :datetime="event.start" :auto-update="60" /></span> 
                                                    <span v-else-if="event.ended">Ended <timeago :datetime="event.end" :auto-update="60" /></span> 
                                                </div>
                                                <div class="mb-2">
                                                    <a @click="slotProps.show(event.event_id)" class="medium-2 stretched-link">{{event.name}}</a>
                                                    <ui-img-dynamic class="mt-2" v-if="event.image" :img-style="{width: '100%'}" :heightRatio="0.5625" :src="event.image"/>
                                                </div> 
                                            </ui-panel>
                                        </vue-horizontal>

                                    </group-events-show>
                            </Teleport>

-->
                            <div class="row">

                                <div class="col-12 col-md-8 mb-1 mx-sm-0 mx-2">

                                    <ui-button :size="'md'" @click="addSomethingToggle('announcement')"  :button-type="'action'" :label="'+ ' + $trans('New post')" ></ui-button>

                                    <ui-button :size="'md'" @click="addSomethingToggle('calendar-event')" class="ml-2"  :button-type="'new'" :label="'+ ' + $trans('Add event')" ></ui-button>

                                    <group-event-new @event-added="newEventAdded" @close="ui.addSomething = null" :happening-id="happeningId" :open="ui.addSomething == 'calendar-event'" />

                                </div>

                            </div>
                            <comment-topic
                                v-if="loadingStatus == 'fetched'"
                                :post-format="'tiptap-json'"
                                @posting-finished="ui.addSomething = null"
                                @cancel-posting="ui.addSomething = null"
                                class="comments mt-2"
                                :topic-comments="this.happening.topic"
                                :fetch-comments-url="'/happenings/' + this.happeningId + '/topic/'"
                                :post-reply-url="'/happenings/' + this.happeningId + '/comment/'"
                                :post-comment-url="'/happenings/' + this.happeningId + '/topic/'"
                                :topic="happening.uuid"
                                :with-subject="true"
                                :offer-hats="false"
                                :calendar-event-choice="true"
                                :email-announcement-choice="true"
                                :post-explainer="$trans('post_explainer')"
                                :show-hats-on-start="false"
                                :add-comment-editor-open="ui.addSomething == 'announcement'">
                            
                            </comment-topic>
                            
                        </div>


                    </div>
                    <div class="d-none d-md-block col col-md-4">

                        <div class="d-block mt-0 mb-0" v-if="events.length > 0">

                            <ui-panel class="mt-0 bg-white pl-4 pr-2 pt-3 pb-5 d-none d-md-block">

                                <h3 class="mb-0">{{$trans('Events coming up')}}</h3>
                                <hr class="mt-0">
                                <group-events-show v-slot="slotProps" :calendar-events="events" @saved="fetchEvents()" ref="showEvents" :happening-id="happeningId">
                                
                                    <div v-for="event in slotProps.events">
                                        <div class="when-is-it small-2">
                                            <span v-if="!event.started">Starts <timeago :datetime="event.start" :auto-update="60" /></span>
                                            <span v-else-if="event.started && !event.ended">Started <timeago :datetime="event.start" :auto-update="60" /></span> 
                                            <span v-else-if="event.ended">Ended <timeago :datetime="event.end" :auto-update="60" /></span> 
                                        </div>
                                        <div class="mb-2">
                                            <a @click="slotProps.show(event.event_id)">{{event.name}}</a>

                                        </div>
                                    </div>

                                </group-events-show>

                            </ui-panel>


                        </div>


                            <div v-if="allGroups.length > 1" class="d-none d-md-block mt-3 mb-0">


                                <ui-panel class="mt-0 bg-white pl-4 pr-2 pt-3 pb-4">

                                    <h3 class="mb-0">{{$trans('Groups')}}</h3>
                                    <hr class="mt-0">

                                    <div class="pl-3 mb-1" v-for="group in allGroups" :key="group.happening_id" >

                                        <ui-a v-if="$parent.$parent.happening.happening_id != group.happening_id " :href="'/group/' + group.happening_id">{{group.name}}</ui-a> 
                                        <span class="tab-link-selected" v-else>{{group.name}}</span>

                                    </div>   
                                </ui-panel>                         

                            </div>


                    </div>


                </div>

            </ui-page-body>
        </template>
    </div>
    
    </div>
</template>

<script>
    import backend from '../../backend.js'
    import { useProfilesStore } from '../../stores/profiles'
    import { mapStores } from 'pinia'

    import { useGroup } from './group.js'
    const group = useGroup()

    import groupRegister from './group-register.vue'
    import groupIntro from './group-intro.vue'
    import groupEventsShow from './group-events-show.vue'
    import groupConfig from './group-config.vue'
    import groupEventNew from './group-event-new.vue'

    import commentTopic from '../comments/comment-topic.vue'

    import imageStream from '../image-stream.vue'

    export default {
        name: 'group',
        data() {
            return {
                events: [],
                newEventValid: false,
                status: 'starting',
                startOfRetrievedHappening: 0,
                endOfRetrievedHappening: 0,
                jsonEditorOptions: {mainMenuBar: false, mode: 'tree'},
                editedConfig: {},
                newConfigString: '',
                initialSectionSet: false,
                ui: {
                    userSetShowSection: false,
                    manageParticipants: false,
                    joiningHappening: false,
                    mode: 'show-happening',
                    addSomething: null
                },
                stage: 'register_form',
                photoStream: [],
            }
        },
        props: {
            startMode: {
                required: false,
                type: String,
                default: 'show-happening'
            },
            translations: {
                required: false,
                type: Object
            },
            happeningId: {
                required: true,
                type: String
            }
        },
        components: {
            commentTopic,
            groupRegister,
            groupIntro,
            groupEventsShow,
            groupConfig,
            groupEventNew,
            imageStream
        },
        computed: {
            userType: function () {
                return this.profilesStore.currentUser.type
            },
            userIdNumber: function () {
                return this.profilesStore.currentUser.user_id
            },
            allGroups: function () {
                return this.$stores.groups.groups
            },
            showSection: {
                get: function () {
                    if (this.ui.userSetShowSection) {
                        return this.ui.userSetShowSection
                    }
                    if (this.currentUserIsRegistered) {
                        return 'announcements'
                    }
                    return 'intro'
                },
                set: function (value) {
                    this.ui.userSetShowSection = value
                }
            },
            loadingStatus: function () {
                return group.status.value
            },
            happening: function () {
                return group.data.value
            },
            ...mapStores(useProfilesStore),
            config: function () {
                return this.happening.config
            },
            configHasBeenEdited: function () {
                return JSON.stringify(this.happening.config) === JSON.stringify(this.editedConfig)
            },
            sections: function () {
                let sections = [
                    {display: this.$trans('Intro'), value: 'intro'},
                    {display: this.$trans('Schedule'), value: 'schedule'},
                    {display: this.$trans('Guide'), value: 'guide'},
                    {display: this.$trans('Participants'), value: 'participants'}
                ]

                if (this.userType === 'admin') {
                    sections.splice(10, 0, {display: this.$trans('Config'), value: 'config'})
                }

                if (this.currentUserIsRegistered || this.userType === 'admin') {
                    sections.splice(0, 0, {display: this.$trans('Announcements'), value: 'announcements'})
                } else {
                }

                return sections
            },
            sectionsSimplified: function () {
                let sections = [
                    {display: this.$trans('Intro'), value: 'intro'},
                    {display: this.$trans('Participants'), value: 'participants'}
                ]

                if (this.userType === 'admin') {
                    sections.splice(10, 0, {display: this.$trans('Config'), value: 'config'})
                }

                if (this.currentUserIsRegistered) {
                    sections.splice(0, 0, {display: this.$trans('Events'), value: 'schedule'})
                    sections.splice(0, 0, {display: this.$trans('Announcements'), value: 'announcements'})

                } else {
                }

                return sections            
            },
            currentUserIsRegistered: function () {
                if (!this.isUserAuthenticated) {
                    return false
                }
                if (this.happening.registrations === undefined) {
                    return false
                }
//                let userId = this.userIdNumber
                if (this.happening.registrations.filter(e => e.user_id === this.userIdNumber).length > 0) {
                    return true
                }

                return false
            },
            participantsString: function () {
                return this.generateStringFromRegistrations('participant')
            },
            mentorsString: function () {
                return this.generateStringFromRegistrations('mentor')
            },
            allMembersString: function () {
                return this.generateStringFromRegistrations()
            }
        },
        methods: {
            newEventAdded: function () {
                this.$refs.showEvents.setup()
            },
            register: function (userId) {
                this.$http.post(this.apiUrl + '/happenings/' + this.happeningId + '/registrations/' + userId + '/', {}, {
                    headers: {
                        'Authorization': this.getAuthorizationHeaderMixin()
                    }
                }).then((response) => {
                    this.fetchHappening()
                    this.registerNotification({
                        message: 'User registered',
                        timeout: 10000,
                        type: 'success'
                    })
                }, (error) => {
                    this.registerNotification({
                        message: error.response.data.message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
            },
            unRegister: function (userId) {
                this.$http.delete(this.apiUrl + '/happenings/' + this.happeningId + '/registrations/' + userId + '/', {
                    headers: {
                        'Authorization': this.getAuthorizationHeaderMixin()
                    }
                }).then((response) => {
                    this.fetchHappening()

                    this.registerNotification({
                        message: 'Participant unregistred',
                        timeout: 10000,
                        type: 'success'
                    })
                }, (error) => {
                    this.registerNotification({
                        message: error.response.data.message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
            },
            updateType: function (type, userId) {
                this.$http.patch(this.apiUrl + '/happenings/' + this.happeningId + '/registrations/' + userId + '/', {type: type}, {
                    headers: {
                        'Authorization': this.getAuthorizationHeaderMixin()
                    }
                }).then((response) => {
                    this.setup()
                    this.registerNotification({
                        message: 'Registration updated',
                        timeout: 10000,
                        type: 'success'
                    })
                }, (error) => {
                    this.registerNotification({
                        message: error.response.data.message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
            },
            closeAddSomething: function () {
                this.ui.addSomething = null
            },
            addSomethingToggle: function (type) {
                if (this.ui.addSomething === type) {
                    this.ui.addSomething = null
                } else {
                    this.ui.addSomething = type
                }
            },
            joinHappening: function () {
                // is there a survey?
                if (this.happening.config.survey && this.happening.config.survey.length > 0) {
                    this.ui.mode = 'join-happening'
                } else {                
                    this.ui.joiningHappening = true
                    backend.post('/happenings/' + this.happeningId + '/registrations/', {
                        user_id: this.profilesStore.currentUser.user_id
                    }).then((response) => {
                        this.setup()
                        this.ui.joiningHappening = false
                    })
                }
            },
            leaveHappening: function () {

                if (!confirm('Are you sure you want to leave this group?')) {
                    return
                }

                backend.delete ('/happenings/' + this.happeningId + '/registrations/' + this.userIdNumber + '/').then((response) => {
                    this.setup()
               })                        
            },
            configUpdated: function (editedConfig) {
                this.editedConfig = editedConfig
                this.updateConfig()
            },
            updateConfig: function () {
                this.$http.post(this.apiUrl + '/happenings/' + this.happeningId + '/config/', {
                    config: this.editedConfig
                }, {
                    headers: {
                        'Authorization': this.getAuthorizationHeaderMixin()
                    }
                }).then((response) => {
                    this.fetchHappening()
                    alert('Configuration has been updated')
                }, (error) => {
                    console.log(error)
                    alert('error ...')
                })
            },
            getCustomLanguage: function (key) {
                if (this.happening.custom_language[key]) {
                    return this.happening.custom_language[key]
                } else {
                    return {}
                }
            },
            generateStringFromRegistrations: function (type = false) {
                let participantsString = ''
                this.happening.registrations.forEach(function(participant) {
                    if (participant.type === type || type === false) {
                        participantsString = participantsString + participant.user_id + ','
                    }
                })
                return participantsString
            },
            setup: function() {
                this.fetchHappening()
                this.fetchPhotoStream()
                this.fetchEvents()

            },
            fetchPhotoStream: function () {
                backend.get('/happenings/' + this.happeningId + '/photo_stream/').then((response) => {
                    this.photoStream = response.data.photo_stream
                }, (error) => {
                    this.registerNotification({
                        message: error.response.data.message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
            },
            fetchEvents: function () {
                if (this.currentUserIsRegistered) {
                    backend.get('/happenings/' + this.happeningId + '/calendar/events/').then(response => {
                    this.events = response.data.calendar_events
                    })
                }
            },
            fetchHappening: function () {

                group.fetch(this.happeningId)
                if (!this.initialSectionSet) {
                        if (this.currentUserIsRegistered) {
                            this.showSection = 'announcements'
                            this.initialSectionSet = true
                        }
                    }

                /*
                backend.get('/happenings/' + this.happeningId + '/',).then((response) => {
//                    this.happening = response.data.happening
                    Object.assign(this.editedConfig, this.happening.config)

                    this.loadingStatus = 'data-loaded'
                    if (!this.initialSectionSet) {
                        if (this.currentUserIsRegistered) {
                            this.ui.showSection = 'announcements'
                            this.initialSectionSet = true
                        }
                    }
                }, (error) => {
                    this.registerNotification({
                        message: error.response.data.message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
                */
            },
            openHeaderlessVideoChat() {
                window.open(`/${this.language}/happening_activity/${this.happeningId}/video_chat/headerless`,
                            'popup',
                            'width=1200, height=800')
            }

        },
        beforeDestroy () {
        },
        created: function() {
            // set this up
            this.setup()
        },
        mounted: function() {
            this.ui.mode = this.startMode
            this.ui.userSetShowSection = false
        }
    }
</script>
