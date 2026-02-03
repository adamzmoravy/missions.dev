<template>
    <div>
        <table>
            <tr>
                <td style="vertical-align: middle;">
                    <label for="calendar-invite-start-date" class="mb-0 mr-2">Start</label>

                </td>
                <td>

                    <input type="date" v-model="eventStartDate" :min="todaysDate" id="calendar-invite-start-date" name="start-date">
                    <input type="time" v-model="eventStartTime" id="calendar-invite-start-time" name="start-time"> 

                </td>
            </tr>
            <tr>
                <td>
                    <label for="calendar-invite-end-date" class="mb-0 mr-2">End</label>

                </td>
                <td>
                    <input type="date" v-model="eventEndDate" :min="eventEndDate" id="calendar-invite-end-date" name="end-date">
                    <input type="time" v-model="eventEndTime" id="calendar-invite-end-time" name="end-time"> 
                </td>
            </tr>
            <tr>
                <td>
                    <label for="calendar-invite-location" class="mb-0 mr-2">Location</label>

                </td>
                <td>
                    <input type="text" style="width:300px;" v-model="eventLocation" id="calendar-invite-location" name="location">
                </td>
            </tr>

        </table>


    </div>
</template>

<script>

export default {
    name: 'calendar-event-picker',
    data() {
        return {
            eventStartDate: null,
            eventEndDate: null,
            eventStartTime: '09:00',
            eventEndTime: null,
            eventLocation: null
        }
    },
    computed: {
        event () {
            return {
                start_date: this.eventStartDate,
                start_time: this.eventStartTime,
                end_date: this.eventEndDate,
                end_time: this.eventEndTime,
                location: this.eventLocation
            }
        },
        todaysDate() {
            return new Date().toISOString().split('T')[0]
        }
    },
    methods: {
        addHoursToTimeOfDay(timeOfDay, hours) {
            let time = timeOfDay.split(':')
            let hour = parseInt(time[0])
            let minute = time[1]
            let newHour = hour + hours
            return `${newHour}:${minute}`
        }
    },
    watch: {
        event: function (newVal) {
            this.$emit('event-update', newVal)
        },
        eventStartDate: function(newVal, oldVal) {
            if (newVal !== null && this.eventEndDate === null) {
                this.eventEndDate = newVal
            }
        },
        eventStartTime: function (newVal, oldVal) {
            if (newVal !== null && this.eventEndTime === null) {
                this.eventEndTime = this.addHoursToTimeOfDay(newVal, 1)
            }
        }
    }
}
</script>
