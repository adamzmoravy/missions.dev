<style scoped>

.time {
    font-weight:800;
}
.session {
    background-color:white;
    justify-content: space-between;
}

</style>

<template>

    <div class="mt-3" v-if="events.length > 0">
        <div v-for="(event, i) in events">
            <div>
                <span v-if="i == 0">
                    <span>{{isWhen(event.start)}}
                    </span>
                </span>
                <span v-else>
                    <span v-if="isWhen(events[i - 1].start) != isWhen(events[i].start)">
                        {{isWhen(events[i].start)}}
                    </span>
                </span>

            </div>
            <div class="my-3 session p-3 comment-container d-md-flex">
                <div class="d-none d-sm-inline organizer">
                    <h3 class="small-2 text-center">Organizer</h3>

                    <div class="d-flex justify-content-md-center mb-2 mb-md-0 w-100">
                        <div class="organizer-profile" >
                            <ui-a :href="'/profile/' + event.user_id"><profile-image-simple :profile="$stores.profiles.getProfilePreviewById(event.user_id)" /></ui-a>
                        </div>
                    </div>
                </div>
                <div style="flex-grow: 1;">
                    <h3 class="large">{{ event.name }}</h3>
                    <div v-if="event.user_id == $currentUser.user_id">
                        <ui-button :size="'sm'" @click="$emit('duplicate', event)" label="Duplicate"></ui-button>
                        <ui-button :size="'sm'" @click="removeEvent(event.event_id)" button-type="cancel" class="ml-2" label="Remove"></ui-button>
                    </div>
                    <span class="time">{{ moment(event.start).format("dddd, MMMM Do YYYY")}}</span>
                    <div class="d-flex align-items-center">
                        <span class="mr-1 material-symbols-outlined md-18">schedule</span>
                        <span>{{ moment(event.start).format("HH:mm")}}</span> -
                        <span>{{ moment(event.end).format("HH:mm")}}</span>
                        <template v-if="event.price && event.price.amount">
                            <span class="ml-2 mr-1 material-symbols-outlined md-18">sell</span>
                            <span>Kr. {{ event.price.amount }}</span>
                        </template>
                    </div>
                
                    <div class="d-flex align-items-center" v-if="countAttending(event.attending)">

                        <span class="material-symbols-outlined md-18">emoji_people</span>
                        <span>
                            <span>{{countAttending(event.attending)}} coming</span> <span v-if="event.max_participants"> of {{event.max_participants}} capacity</span>
                        </span>
                    </div>

                    <div class="d-flex align-items-center" v-else-if="event.max_participants">
                        <span class="material-symbols-outlined md-18">emoji_people</span>
                        <span>
                            0 attending of max {{event.max_participants}} participants.
                        </span>
                    </div>
                    <div class="d-flex align-items-center" v-if="event.location">
                        <span class="material-symbols-outlined md-18">location_on</span>
                        <a :href="event.location" class="location" v-if="isValidUrl(event.location)">
                            {{event.location}}
                        </a>
                        <span class="location" v-else>
                            {{event.location}}
                        </span>
                    </div>
                </div>
                <div  style="flex-grow: 1;" class="pl-md-4">

                    <profile-index-row
                        class="w-100"
                        v-if="getUserIds(event.attending).length > 0"
                        :profiles="$stores.profiles.getProfilePreviewsByIds(getUserIds(event.attending))"
                        :profile-size="40"
                        :max-show-num-profiles="15"
                        :show-name="false"
                        @show-all-profiles="showProfileModal = event.event_id"
                        @profiles-fit=""
                    />


                    <ui-modal title="Attending" :open="showProfileModal == event.event_id" @close="showProfileModal = null">

                        <template #body>
<!--
                            <profile-index
                                :limit="0"
                                :user-ids="getUserIds(event.attending)"
                                :profile-size="40"
                                type="list">
                            </profile-index>

-->

                        </template>


                    </ui-modal>


                </div>
                
                <div class="interact">
                    <div class="d-flex"> 
                            <ui-button :disabled="hoursToGo(event.start) < 0 || maxCapacityReached(event.attending, event.max_participants)" @click="plusOne(event.calendar_event_id)" class="ml-0 ml-md-auto mr-2" button-type="new" v-if="isAttending(event.attending)">+ 1</ui-button>
                            <ui-button :disabled="hoursToGo(event.start) < 0 || maxCapacityReached(event.attending, event.max_participants)" @click="addMe(event.calendar_event_id)" v-if="!isAttending(event.attending)">Sign up</ui-button>
                            <ui-button :disabled="hoursToGo(event.start) < 0" @click="removeMe(event.calendar_event_id)" v-else button-type="cancel">Cancel</ui-button>
                    </div>
                    <div class="w-100 pt-2" v-if="isAttending(event.attending)">You are attending,<br> with {{myPluses(event.attending)}} guests.</div>
                </div>

            </div>

        </div>

    </div>

</template>

<style scoped>

    .organizer-profile {
        max-width:70%;
    }
    .location {
        max-width:220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .organizer {
        max-width:10rem;
        border-right: 1px solid #ccc;
        padding-left:0.5em;
        padding-right:1em;
        margin-right:2em;
    }

    .interact {
        border-left: 1px solid #ccc;
        padding-left:2em;
        padding-right:1em;
        margin-left:2em;
    }

    /** Screen size less than 768 */

    @media screen and (max-width: 768px) {
        .organizer {
            border-right: none;
            padding-left:0;
            padding-right:0;
            margin-right:0;
        }
        .interact {
            border-left: none;
            padding-left:0;
            padding-right:0;
            margin-left:0;
        }
    }

</style>

<script>
import backend from '../../backend.js'
import moment from 'moment'
export default {
    name: 'session-signup',
    methods: {
        moment: function (date) {
            return moment(date)
        },
        isValidUrl: function (url) {
            return url.match(/(http(s?))\:\/\//)
        },
        getEvents: async function () {
            let results = await backend.get('/calendar/events/participate/')
            this.events = results.data.calendar_events
        },
        removeEvent: function (eventId) {

            if (confirm('Are you sure you want to delete this event?')) {

                backend.delete('/calendar/events/' + eventId + '/')
                            .then(response => {
                                this.$emit('saved')
                                this.getEvents()
                            })            
            }

        },
        removeMe: function (eventId) {
            backend.delete('/calendar/events/attendance/' + eventId + '/me/')
                            .then(response => {
                                this.$emit('saved')
                                this.getEvents()
                            })            
        },
        addMe: function (eventId) {

            backend.post('/calendar/events/attendance/' + eventId + '/me/')
                            .then(response => {
                                this.$emit('saved')
                                this.getEvents()
                            })            
        },
        plusOne: function (eventId) {
            backend.post('/calendar/events/attendance/' + eventId + '/me/1/')
                            .then(response => {
                                this.$emit('saved')
                                this.getEvents()
                            })            
        },
        countAttending: function (attending) {
            let count = 0
            for (const [key, value] of Object.entries(attending)) {
                count += attending[key].plus + 1
            }
            return count
        },
        maxCapacityReached: function (attending, max_capacity) {
            if (!max_capacity) {
                return false
            }
            return this.countAttending(attending) >= max_capacity
        },
        isAttending: function (attending) {
            if (attending.hasOwnProperty(this.$currentUser.user_id.toString())) {
                return true
            }
            return false
        },
        myPluses: function (attending) {
            return attending[this.$currentUser.user_id.toString()].plus
        },
        getUserIds: function (attending) {
            return Object.getOwnPropertyNames(attending)
        },
        isWhen: function (datetime) {
            if (this.isToday(datetime)) {
                return 'Today'
            }
            if (this.isTommorrow(datetime)) {
                return 'Tommorrow'
            }
            if (this.isSoon(datetime)) {
                return 'Next 7 days'
            }
            if (this.isLater(datetime)) {
                return 'Later'
            }
        },
        hoursToGo: function (dateTime) {
            let now = new Date()
            let then = new Date(dateTime)
            let diff = then - now
            let hours = Math.floor(diff / 1000 / 60 / 60)
            return hours
        },
        daysAgo: function (datetime) {
            let now = new Date()
            let then = new Date(datetime)
            let diff = now - then
            return Math.floor(diff / (1000 * 60 * 60 * 24))
        },
        isToday: function (datetime) {
            let today = new Date()
            let eventDate = new Date(datetime)
            return today.toDateString() == eventDate.toDateString()
        },
        isTommorrow: function (datetime) {
            let now = new Date()
            let tomorrow = new Date(now)
            tomorrow.setDate(tomorrow.getDate() + 1)
            let eventDate = new Date(datetime)
            return eventDate.getDate() == tomorrow.getDate() && eventDate.getMonth() == tomorrow.getMonth() && eventDate.getFullYear() == tomorrow.getFullYear()
        },
        isSoon: function (datetime) {
            if (-this.daysAgo(datetime) <= 7) {
                return true
            }
        },
        isLater: function (datetime) {
            if (-this.daysAgo(datetime) > 7) {
                return true
            }
        }
    },
    mounted: function() {
        this.getEvents()
    },
    computed: {
    },
    data() {
        return {
            showProfileModal: null,
            events: [],
            sessions: [
                {
                    date: 'Wednesday, Oct 12th 2022',
                    attending: ["816", "815", "814", "803"]
                },
                {
                    date: 'Thursday, Oct 13th 2022',
                    attending: ["814", "803", "815"]
                },
                {
                    date: 'Friday, Oct 14th 2022',
                    attending: ["814", "803"]
                },
                {
                    date: 'Monday, Oct 17th 2022',
                    attending: ["816", "814"]
                }            ]
        }
    }
}
</script>