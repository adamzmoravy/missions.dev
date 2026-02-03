<template>

    <div class="all-container">
        <div class="monitor-container">

            <div class="top" style="height:7%;">
                    <img style="height:100%;" 
                    src="https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_1000,e_pixelate:0/https://s3-eu-west-1.amazonaws.com/idea-village-image-host/sandbox/c0c448af-7fc3-47f6-8b9d-1994c2c904a9.png">
            </div>


            <div class="bottom" v-if="events.length > 0">

                <h3 class="large coming-up">{{$trans('Events coming up')}}</h3>

                <group-events-show v-slot="slotProps" class="mr-2" :calendars="['happening-', 'platform', 'submission-']" :calendar-events="events" ref="showEvents">                        
                    <div class="row">
                        <template v-for="(event, index) in slotProps.events" :key="event.event_id">

                            <div v-if="index == 0" class="first-event col-12 mb-3 position-relative">
                                <ui-panel class="bg-white">
                                    <div class="when-is-it">
                                        <span v-if="!event.started">Starts <timeago :datetime="event.start" :auto-update="60" /></span>
                                        <span v-else-if="event.started && !event.ended">Started <timeago :datetime="event.start" :auto-update="60" /></span> 
                                        <span v-else-if="event.ended">Ended <timeago :datetime="event.end" :auto-update="60" /></span> 
                                    </div>
                                    <div class="mb-2">
                                        <a @click="slotProps.show(event.event_id)" class="large stretched-link event-name">{{event.name}}</a>
                                        <ui-img-dynamic class="mt-2" v-if="event.image" :img-style="{width: '100%'}" :heightRatio="0.5625" :src="event.image"/>
                                    </div> 
                                </ui-panel>
                            </div>
                            <div v-else class="col-6 mb-4 position-relative">
                                <ui-panel class="bg-white">
                                    <div class="when-is-it">
                                        <span v-if="!event.started">Starts <timeago :datetime="event.start" :auto-update="60" /></span>
                                        <span v-else-if="event.started && !event.ended">Started <timeago :datetime="event.start" :auto-update="60" /></span> 
                                        <span v-else-if="event.ended">Ended <timeago :datetime="event.end" :auto-update="60" /></span> 
                                    </div>
                                    <div class="mb-2">
                                        <a @click="slotProps.show(event.event_id)" class="large stretched-link event-name">{{event.name}}</a>
                                        <ui-img-dynamic class="mt-2" v-if="event.image" :img-style="{width: '100%'}" :heightRatio="0.5625" :src="event.image"/>
                                    </div> 
                                </ui-panel>
                            </div>


                        </template>
                    </div>

                </group-events-show>   


            </div>

            </div>
    </div>
 

</template>

<style scoped>
.coming-up {
    margin-bottom:2rem;
    font-size:200%!important;
}

.first-event .when-is-it {
    font-size:200%;
}
.first-event .event-name {
    font-size:250%!important;
    display: block;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.x-large {
    line-height:100%;
}
.event-details {
    padding:4%;
    height:calc(100% - 6rem);
}

.event-description {
    max-height:100%;
    overflow:hidden;
}
.top {
    display:flex;
    justify-content: center;
}

.bottom {
    padding-bottom:6%;

}
.middle {
    overflow:hidden;
    flex-grow:1;
}
.all-container {
    height:100vh;
    position:fixed;
    width:100vw;
    top:0;
    left:0;
}
.monitor-container {
    padding:3.5%;
    padding-top:4%;
    width:100%;
    height:100%;
    overflow:auto;
    display:flex;
    flex-direction: column;
    overflow:hidden;
    padding-bottom:1rem;
}

.coming-up-container {
    gap:1rem;
}

.coming-up {
    min-width:38%;
}
</style>

<script>

import moment from 'moment'

import backend from '../backend'
import groupEventsShow from '../components/groups/group-events-show.vue'
import calendarPickDay from '../components/calendar/calendar-pick-day.vue'

export default {
    name: 'MonitorPage',
    data: function () {
        return {
            events: [],
            showEventDetails: null
        }
    },
    components: {
        groupEventsShow,
        calendarPickDay
    },
    methods: {
        setup: function () {
            backend.get('/calendar/events/').then((response) => {
                this.events = response.data.calendar_events
            })
        },
        hasDateTimeStarted: function (dateTime) {
            return moment(dateTime).isBefore(moment())
        },
        dateTimeToHumanFriendly: function (dateTime) {
            return moment(dateTime).format('dddd, MMMM Do YYYY, h:mm a')
        }
    },
    computed: {
        moment: function () {
            return moment
        },
        startDate: function () {
            if (this.eventDetails == null) {
                return null
            }
            return moment(this.eventDetails.start).format('YYYY-MM-DD')
        },
        endDate: function () {
            if (this.eventDetails == null) {
                return null
            }
            return moment(this.eventDetails.end).format('YYYY-MM-DD')
        },
        startTime: function () {
            if (this.eventDetails == null) {
                return null
            }
            return moment(this.eventDetails.start).format('HH:mm')
        },
        endTime: function () {
            if (this.eventDetails == null) {
                return null
            }
            return moment(this.eventDetails.end).format('HH:mm')
        },
        userProfile: function () {
            return this.$stores.auth.userData
        },
        eventDetails: function () {
            if (this.events.length == 0) {
                return null
            }
            return this.events[0]
        },
        eventDetailsDates: function () {
            if (this.eventDetails == null) {
                return null
            }

            let end
            let start = moment(this.eventDetails.start).format('dddd, MMMM Do YYYY, HH:mm')

            // if this.eventDetails.start is the same date as this.eventDetails.end
            // then we only need to show the start date
            if (moment(this.eventDetails.start).isSame(this.eventDetails.end, 'day')) {
                end = moment(this.eventDetails.end).format('HH:mm')
            } else {
                end = moment(this.eventDetails.end).format('dddd, MMMM Do YYYY, HH:mm')
            }

            return {
                start: start,
                end: end    
            }

        }
    },
    mounted: function () {
        this.setup()

        setInterval(() => {
            this.setup()
        }, 1000 * 60)

        setInterval(() => {
            location.reload()
        }, 1000 * 60 * 60)

    }
}

</script>