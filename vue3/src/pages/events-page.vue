<template>


    <div>


        <ui-page-body class="mt-4">
            
                <ui-button class="mr-2 mb-3" v-if="$isUserAuthenticated" button-type="new" size="md" @click="$stores.ui.setNewEvent()">+ {{ $trans('Event') }}</ui-button>

                <group-events-show v-slot="slotProps" class="mr-2" :calendars="['happening-', 'platform', 'submission-']" :day-picked="day" :calendar-events="events" @saved="fetchEvents()" ref="showEvents">                        
                    <div class="row">
                        <div class="col-md-7">
                            <div v-if="day" class="d-flex align-items-center">
                                <h1 class="large">{{ dayHuman }} <ui-button size='sm' class="ml-2" @click="day=null">clear</ui-button></h1>
                            </div>
                            <hr class="mt-0" v-if="day">
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
                    </div>
                <div class="col-md-5">
                    <ui-panel class="bg-white">
                        <calendar-pick-day :events="slotProps.allEvents" @event-id-selected="slotProps.show" @day-picked="dayPicked" class="bg-white p-3" />
                    </ui-panel>

                    </div>
                </div>

            </group-events-show>        

        </ui-page-body>
    </div>

</template>

<script>

import backend from '../backend'
import groupEventsShow from '../components/groups/group-events-show.vue'
import calendarPickDay from '../components/calendar/calendar-pick-day.vue'
export default {
    name: "events-page",
    data() {
        return {
            ui: {},
            day: null
        }
    },
    components: {
        groupEventsShow,
        calendarPickDay
    },
    computed: {
        events() {
            return this.$stores.groups.calendarEvents
        },
        dayHuman() {
            if (this.day) {
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(this.day).toLocaleDateString(undefined, options);
            }
            return ''
        },
        daysWhereSomethingHappens() {
            return this.events.map(event => event.start)
        }
    },
    methods: {
        dayPicked(day) {
            this.day = day

        },
        fetchEvents() {
            
            backend.get('/calendar/events/').then((response) => {
                this.events = response.data.calendar_events
            })

        }
    },
    watch:{
        $route (to, from){
            if (to.name == 'events') {
//                this.$refs.resources.setup()
            }
        }
    },
    mounted() {
        this.fetchEvents()
    }
}

</script>