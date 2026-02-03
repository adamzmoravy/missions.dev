<template>

    <div>
        <div class="d-lg-flex date-container">
            <div class="d-lg-flex">
                <div class="mb-2">
                    <label for="start">{{$trans('Start date')}}</label>
                    <input v-model="timespanStart.date" id="start" type="date" class="form-control form-lg">
                </div>
                <div class="ml-lg-2 mb-2">
                    <label for="time">{{$trans('Start time')}} (UTC)</label>
                    <input v-model="timespanStart.time" id="time" type="time" class="form-control form-lg">
                </div>
            </div>
            <div class="d-lg-flex mb-2 arrow-between d-none">
                <span class="large">→</span>
            </div>
            <div class="d-lg-flex mb-2">
                <div class="mb-2">
                    <label for="start">{{$trans('End date')}}</label>
                    <input v-model="timespanEnd.date" id="start" type="date" class="form-control form-lg">
                </div>
                <div class="ml-lg-2 mb-2">
                    <label for="time">{{$trans('End time')}} (UTC)</label>
                    <input v-model="timespanEnd.time" id="time" type="time" class="form-control form-lg">
                </div>
            </div>
        </div>
        <div v-if="timeSpanHumanFriendly != ''" class="pt-1 pb-3">
            <span>Event lasts for <strong>{{ timeSpanHumanFriendly }}</strong> and {{timeToEventHumanFriendly}}</span>
        </div>
    </div>



</template>

<style scoped>

.arrow-between {
    align-items: center;
    justify-content: center;
}
.date-container {
    justify-content: space-between;
}

</style>

<script>
import moment from 'moment'

export default {
    name: 'ui-timespan-selector',
    data: function() {
        return {
            timespanStart: {
                date: null,
                time: null
            },
            timespanEnd: {
                date: null,
                time: null
            }
        }
    },
    computed: {
        validStartAndEndTimes: function() {
            if (!this.startTimestamp || !this.endTimestamp) {
                return false
            }
            if (this.startTimestamp >= this.endTimestamp) {
                return false
            }
            else {
                return {
                    start: this.startDateTime,
                    end: this.endDateTime
                }
            }
        },
        startDateTime: function() {
            return this.timespanStart.date + ' ' + this.timespanStart.time
        },
        endDateTime: function() {
            return this.timespanEnd.date + ' ' + this.timespanEnd.time
        },
        startTimestamp: function() {
            return moment(this.startDateTime).unix()
        },
        endTimestamp: function() {
            return moment(this.endDateTime).unix()
        },
        timeToEventHumanFriendly: function () {

            if (this.endTimestamp < moment().unix()) {
                return 'ended already.'
            }

            if (this.startTimestamp < moment().unix()) {
                return 'started already.'
            }

            // calculate amount of time between now and this.startDateTime and return a human friendly string
            let duration = moment.duration(this.startTimestamp - moment().unix(), 'seconds')
           
            return 'starts in ' + this.humanFriendlyTimeSpan(duration)
        },
        timeSpanHumanFriendly: function () {

            if (!this.validStartAndEndTimes) {
                return ''
            }

            let duration = moment.duration(this.endTimestamp - this.startTimestamp, 'seconds')

            return this.humanFriendlyTimeSpan(duration)
        }
    },
    methods: {
        humanFriendlyTimeSpan (seconds) {

            let duration = seconds
            let years = duration.years()
            let months = duration.months()
            let days = duration.days()
            let hours = duration.hours()
            let minutes = duration.minutes()
            let humanFriendly = ''

            if (years > 0) {
                humanFriendly += years + ' years '
            }
            if (months > 0) {
                humanFriendly += months + ' months '
            }
            if (days > 0) {
                humanFriendly += days + ' days '
            }
            if (hours > 0) {
                humanFriendly += hours + ' hours '
            }
            if (minutes > 0 && days == 0) {
                humanFriendly += minutes + ' minutes '
            }
            return humanFriendly

        }
    },
    watch: {
        validStartAndEndTimes: {
            handler: function(newVal, oldVal) {
                if (newVal) {
                    this.$emit('times', newVal)
                }
            }, 
            deep: true
        },
        timespanStart: {
            handler: function(newVal, oldVal) {
                if (this.timespanEnd.date == null || moment(this.timespanEnd.date).isBefore(moment(this.timespanStart.date))) {
                    this.timespanEnd.date = this.timespanStart.date
                }
                if (this.timespanEnd.time == null || this.startTimestamp > this.endTimestamp) {
                    //this.timespanEnd.time = this.timespanStart.time
                    // add 1 hour to the start time
                    this.timespanEnd.time = moment(this.timespanStart.time, 'HH:mm').add(1, 'hours').format('HH:mm')
                }
            },
            deep: true
        },
        timespanEnd: {
            handler: function() {
                this.$emit('timespan-end', this.timespanEnd)
            },
            deep: true
        }
    },
    props: {
        defaultStart: {
            type: String,
            default: null
        },
        defaultEnd: {
            type: String,
            default: null
        }
    },
    created: function() {
        if (this.defaultStart) {
            this.timespanStart.date = moment(this.defaultStart).format('YYYY-MM-DD')
            this.timespanStart.time = moment(this.defaultStart).format('HH' + ':' + 'mm')
        } else {
            this.timespanStart.time = moment().add(1, 'hours').format('HH' + ':' + '00')            
        }
        if (this.defaultEnd) {
            this.timespanEnd.date = moment(this.defaultEnd).format('YYYY-MM-DD')
            this.timespanEnd.time = moment(this.defaultEnd).format('HH' + ':' + 'mm')
        }


    }    

}

</script>