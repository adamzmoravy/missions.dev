<template>

        <ui-modal :open="eventDetails != null" size="tall" @close="$emit('closed')"  title="Event details">
            
            <template #body>

                <div class="w-100">

                    <div class="w-100 mb-2" style="max-height:100%;" v-if="eventDetails.image">
                        <ui-img-dynamic :img-style="{maxHeight: '300px', width: '100%', objectFit: 'contain'}"  :heightRatio="null" :src="eventDetails.image"/>
                    </div>                 
   
                    <div>                
                        <h3 class="large">{{ eventDetails.name }}</h3>
                    </div>
                    
                    <div v-if="eventDetails.user_id == userProfile.user_id || isUserAdmin" class="mb-2">
                    

                        <ui-button :size="'sm'" @click="removeEvent(eventDetails.event_id)" button-type="cancel"  label="Remove"></ui-button>

                    </div>
                    <div>
                        <p class="medium-2 mb-0 d-flex align-items-center">
                            <span class="material-symbols-outlined md-24 symbol-icon">schedule</span> {{ eventDetails.timesHuman.start }} - {{ eventDetails.timesHuman.end }} (UTC timezone)</p>
                    </div>
                    <div>
                        <p class="medium-2 mb-1 d-flex align-items-center">
                            <span class="material-symbols-outlined md-24 symbol-icon">location_on</span>
                           
                            {{ eventDetails.location }}
                        </p>

                    </div>

                    <div>
                        <p style="white-space: pre-line;width:100%;"  class="mt-3 whitespace-pre comment-text">{{ eventDetails.description }}</p>
                    </div>

                    <div class="pt-2">
                        <h4 class="medium">Submitted by</h4>
                        <hr class="my-2">
                        <profile-index-row v-if="eventDetails.user_id" type="list" :show-name="true" :profiles="$stores.profiles.getProfilePreviewsByIds([eventDetails.user_id])" />

                    </div>



                </div>



            </template>

            <template #footer>

                <div class="d-flex justify-content-center">

                    <session-signup v-if="false" ref="sessionSignup"></session-signup>

                    <add-to-calendar-button
                            class="mb-2"
                            styleLight="--btn-shadow: none;--btn-shadow-hover: none;--btn-shadow-active: none;"
                            :name="eventDetails.name"
                            options="'Google','Apple','Microsoft365', 'MicrosoftTeams', 'Outlook.com'"
                            :location="eventDetails.location"
                            :startDate="startDate"
                            :endDate="endDate"
                            :startTime="startTime"
                            :endTime="endTime"
                            listStyle="overlay"
                            buttonStyle="round"
                            trigger="click"
                            hideIconModal
                            hideTextLabelButton
                            buttonsList
                            size="6"
                            lightMode="system"

                            ></add-to-calendar-button>                
                        </div>

            </template>
            
        </ui-modal>

</template>

<script>

import 'add-to-calendar-button'
import backend from '../../backend.js'
import moment from 'moment'

import sessionSignup from './session-signup.vue'

export default {
    name: 'calendar-event-details',
    props: {
        eventDetails: {
            required: false,
            default: null
        }
    },
    methods: {
        removeEvent: function (eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                backend.delete('/calendar/events/' + eventId + '/')
                    .then(response => {
                        this.$emit('saved')
                    })            
            }
        }
    },
    components: {
        sessionSignup
    },
    computed: {
        isUserAdmin: function () {
            return this.$currentUser?.user_type == 'admin'
        },
        $moment: function () {
            return moment
        },
        startDate: function () {
            if (this.eventDetails == null) {
                return null
            }
            return this.$moment(this.eventDetails.start).format('YYYY-MM-DD')
        },
        endDate: function () {
            if (this.eventDetails == null) {
                return null
            }
            return this.$moment(this.eventDetails.end).format('YYYY-MM-DD')
        },
        startTime: function () {
            if (this.eventDetails == null) {
                return null
            }
            return this.$moment(this.eventDetails.start).format('HH:mm')
        },
        endTime: function () {
            if (this.eventDetails == null) {
                return null
            }
            return this.$moment(this.eventDetails.end).format('HH:mm')
        },
        userProfile: function () {
            return this.$stores.auth.userData
        }
    }

}

</script>