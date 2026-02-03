<template>

    <div class="event-text">
        <span v-for="event in events"><span class="date">{{ dateOnlyHumanFriendly(event.start) }}:</span> <span class="name">{{ event.name }}</span> • </span>
        <br>
        <span style="font-size:300%;margin-top:150px;"><span class="date">May 23rd 2024 13:30:</span> <span class="name">All members' meeting in Wasabi</span></span>
    </div>

</template>

<style scoped>
.event-text {
    text-align: justify;

    padding:5rem;
    font-size: 1em;
    line-height: 200%;
}
.date {
    font-weight: bold;
    color: #840000;
}
.name {
    font-weight: bold;
}
</style>

<script>
import backend from '../backend.js'

export default {
    name: 'EventsPosterPage',
    data: function () {
        return {
            events: [],
            showEventDetails: null
        }
    },
    methods: {
        setup: function () {
            backend.get('/happenings/37/calendar/past_events/2023-06-23/').then(response => {

                this.events = response.data.calendar_events

                // remove the last three events from this.events
                this.events = this.events.slice(0, this.events.length - 3)

            })
        },
        hasDateTimeStarted: function (dateTime) {
            return this.$moment(dateTime).isBefore(this.$moment())
        },
        dateTimeToHumanFriendly: function (dateTime) {
            return this.$moment(dateTime).format('dddd, MMMM Do YYYY, h:mm a')
        },
        dateOnlyHumanFriendly: function (dateTime) {
            return this.$moment(dateTime).format('MMMM Do YYYY')
        }
    },
    mounted: function () {
        this.setup()
    }
}

</script>