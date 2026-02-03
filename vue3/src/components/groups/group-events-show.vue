<template>

            <div>

                <slot :events="eventsToShow" :all-events="allEvents" :show="showEvent"></slot>

                <calendar-event-details @saved="setup()" @closed="showEventDetails = null" :event-details="eventDetails"></calendar-event-details>

            </div>



</template>

<style>

.symbol-icon {
    margin-right: 3px;
}

.when-is-it {

 }
</style>

<script>
import 'add-to-calendar-button'
import { useCalendar } from '../../calendar.js'
var calendar = useCalendar()

import calendarEventDetails from '../calendar/calendar-event-details.vue'

import backend from '../../backend.js'
export default {
    name: 'group-events-show',
    data: function () {
        return {
            showEventDetails: null
        }
    },
    components: {
        calendarEventDetails
    },
    watch: {
        calendarEvents: function (newEvents) {
            calendar.setEvents(newEvents)
        }
    },
    computed: {
        calendar: function () {
            return calendar
        },
        allEvents: function () {
            let events = calendar[this.eventsFilter].value
            if (this.calendars.length > 0) {
                events = events.filter(event => {
                    return this.calendars.some(calendar => {
                        return event.calendars.some(eventCalendar => {
                            return eventCalendar.startsWith(calendar)
                        })
                    })
                })
            }
            return events
        },
        eventsToShow: function () {
            let events = calendar[this.eventsFilter].value

            // filter only events where the array events.calendars contains any of the calendars in this.calendars, where where it is enough that the string in events.calendars starts with the string in this.calendars
            if (this.calendars.length > 0) {
                events = events.filter(event => {
                    return this.calendars.some(calendar => {
                        return event.calendars.some(eventCalendar => {
                            return eventCalendar.startsWith(calendar)
                        })
                    })
                })
            }

            // if this.dayToShow date is set, filter only events that are on that day using the date part of the start date
            if (this.dayPicked != null) {
                events = events.filter(event => {
                    return new Date(event.start).toDateString() == this.dayPicked.toDateString()
                })
            }

            return events
        },
        eventDetails: function () {
            if (this.showEventDetails == null) {
                return null
            }
            return this.calendarEvents.find(event => event.event_id == this.showEventDetails)
        }
    },
    props: {
        eventsFilter: {
            required: false,
            default: 'upComingEvents'
        },
        dayPicked: {
            required: false,
            type: Date,
            default: null
        },
        calendars: {
            required: false,
            default: function () {
                return ['']
            }
        },
        calendarEvents: {
            required: false,
            default: function () {
                return []
            }
        },
        happeningId: {
            required: false,
            type: String,
            default: null
        }
    },
    methods: {
        showEvent: function (eventId) {
            this.showEventDetails = eventId
        },
        setup: function () {
            this.showEventDetails = null
            this.$emit('saved')
            calendar.setEvents(this.calendarEvents)
        }
    },
    mounted: function () {
        this.setup()
    }
}
</script>