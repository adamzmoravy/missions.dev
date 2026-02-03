<template>

    <div class="all-container">
        <div class="monitor-container">
                <div class="message-overlay">
                    <center>
                        <h1 class="x-large">All members' meeting</h1>

                        <h2 class="large mt-4"><strong>This Thursday 13:30 in Wasabi</strong></h2>                    
                        <h2 class="large mt-4"><strong>See you <timeago :datetime="startDateTime" :auto-update="60"></timeago></strong></h2>
                        (That's {{ secondsToGo }} seconds from now)                 

                    </center>
                </div>
                <profile-index class="d-flex w-100 flex-wrap mt-3"
                    inline-template
                    :all-public-platforms="false"
                    :common="$parent.common"
                    api-url="${agitator_url}"
                    language="${lang}"
                    placeholder-image-url="/img/profile-pic.jpg"
                    limit="999"
                    :user-ids="allMembersString"
                    profile-size="150"
                    :show-name="false"
                    :stack-profiles="false"
                    :max-profiles-display="8"
                    :randomize-pulse="true"
                >

                <div class="w-100">

                    <profile-image v-for="profile in randomizedProfiles" :profile="profile" style="margin-right:25px;margin-bottom:25px;width:150px;"></profile-image>

                </div>

             </profile-index>            
        </div>
        <div class="monitor-container d-none">

            <div class="top" style="height:10%;">
                    <img style="height:100%;" 
                    src="https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_1000,e_pixelate:0/https://s3-eu-west-1.amazonaws.com/idea-village-image-host/sandbox/c0c448af-7fc3-47f6-8b9d-1994c2c904a9.png">
            </div>

            <div class="middle pl-2" v-if="eventDetails">
                <h4 class="large">{{$trans('Next event')}}:</h4>
                <ui-panel class="bg-white event-details">
                    <div class="when-is-it x-large mb-4"><span v-if="!hasDateTimeStarted(eventDetails.start)">Starts</span><span v-else>Started</span> <timeago :datetime="eventDetails.start" :auto-update="60" /></div>                
                    <div class="w-100 mb-4" style="max-height:100%;" v-if="eventDetails.image">
                        <img-dynamic :img-style="{maxHeight: '20%', width: '100%', objectFit: 'contain'}"  :heightRatio="null" :src="eventDetails.image"/>
                    </div>                 

                    <h3 class="x-large mb-4">{{ eventDetails.name }}</h3>

                        <div>
                            <p class="large-2 mb-0 d-flex align-items-center">
                                <span class="material-symbols-outlined md-48 symbol-icon">schedule</span> {{ eventDetailsDates.start }} - {{ eventDetailsDates.end }}
                            </p>
                         </div>
                        <div>
                            <p class="large-2 mb-1 d-flex align-items-center">
                                <span class="material-symbols-outlined md-48 symbol-icon">location_on</span>
                            
                                {{ eventDetails.location }}
                            </p>

                        </div>

                        <div class="medium event-description">
                            <p style="white-space: pre-line;width:100%;"  class="mt-3 large whitespace-pre">
                                {{ eventDetails.description }}
                             </p>
                        </div>


                </ui-panel>
            </div>

            <div class="bottom" v-if="events.length > 1">
                <h3 class="pl-2 large">{{$trans('Other events coming up')}}</h3>

                <div class="pl-2 d-flex coming-up-container">

                    <ui-panel v-for="event in events.slice(1)" class="bg-white coming-up">

                        <div class="when-is-it"><span v-if="!hasDateTimeStarted(event.start)">Starts</span><span v-else>Started</span> <timeago :datetime="event.start" :auto-update="60" /></div>
                        <div class="mb-2 medium-2">
                            <a @click="showEventDetails = event.event_id">{{event.name}}</a>
                        </div>
                        <div>
                            <p class="small-2 mb-0 d-flex align-items-center">
                                <span class="material-symbols-outlined md-18 symbol-icon mr-1">schedule</span> {{ dateTimeToHumanFriendly(event.start) }}
                            </p>
                        </div>
                        <div>
                            <p class="small-2 mb-1 d-flex align-items-center">
                                <span class="material-symbols-outlined md-18 symbol-icon mr-1">location_on</span>                           
                                {{ event.location }}
                            </p>

                        </div>
                        <div>
                            <p class="mt-2">{{ event.description }}</p>
                        </div>

                    </ui-panel>

                </div>

            </div>

            </div>
    </div>
 

</template>

<style scoped>
.message-overlay {
    padding-top:5vh;
    padding-bottom:5vh;

    z-index:1000;
    position:absolute;
    top:30vh;
    left:33vw;
    width:53vw;
    background-color:white;

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
    width:120vw;
}
.monitor-container {
    position:absolute;
    top:-10vh;
    left:-10vw;
    width:120%;
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
import backend from '../backend.js'

export default {
    name: 'MonitorMembersPageV2024',
    data: function () {
        return {
            events: [],
            showEventDetails: null,
            startDateTime: '2024-05-23T13:30:00',
            secondsToGo: 0
        }
    },
    methods: {
        setup: function () {
            backend.get('/monitor/haus/calendar/events/').then(response => {

                this.events = response.data.calendar_events

            })
        },
        hasDateTimeStarted: function (dateTime) {
            return this.$moment(dateTime).isBefore(this.$moment())
        },
        dateTimeToHumanFriendly: function (dateTime) {
            return this.$moment(dateTime).format('dddd, MMMM Do YYYY, h:mm a')
        }
    },
    computed: {
        secondsUntilStart: function () {
            return this.$moment(this.startDateTime).diff(this.$moment(), 'seconds')
        },
        timeInDaysAndHoursUntilStart: function () {
            return this.$moment(this.startDateTime).fromNow()
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
            let start = this.$moment(this.eventDetails.start).format('dddd, MMMM Do YYYY, HH:mm')

            // if this.eventDetails.start is the same date as this.eventDetails.end
            // then we only need to show the start date
            if (this.$moment(this.eventDetails.start).isSame(this.eventDetails.end, 'day')) {
                end = this.$moment(this.eventDetails.end).format('HH:mm')
            } else {
                end = this.$moment(this.eventDetails.end).format('dddd, MMMM Do YYYY, HH:mm')
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

        setInterval(() => {
            this.secondsToGo = this.$moment(this.startDateTime).diff(this.$moment(), 'seconds')
        }, 1)

    }
}

</script>