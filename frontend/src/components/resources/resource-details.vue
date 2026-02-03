<template>
    <div>

        <ui-page-header>
            <div>
            <h2 class="mt-4 mb-2">{{resource.name}}</h2>

            <div class="d-flex align-items-center medium-2 mb-3">
                <span class="ml-0 mr-1 material-symbols-outlined md-30">sell</span> {{resource.price}} kr. / hour
            </div>

            <ui-button @click="ui.showBookingModal = true">Book</ui-button>

            <ui-modal :title="$trans('Booking details')" :open="ui.showBookingDetails" @close="ui.showBookingDetails = null">

                <template v-slot:body>

                    <div>


                                <table class="booking-details-table w-100 medium-2">
                                    <tr>
                                        <td class="pr-3"><strong>Description</strong></td>
                                            <td>{{ bookingBeingPreviewed.description }}</td>
                                    </tr>

                                    <tr>
                                        <td class="pr-3"><strong>Booked by</strong></td>
                                        <td><ui-a class="underline" :href="'/profile/' + bookingBeingPreviewed.bookedBy.user_id">{{ bookingBeingPreviewed.bookedBy.first_name }} {{ bookingBeingPreviewed.bookedBy.last_name }}</ui-a></td>
                                    </tr>
                                    <tr>
                                        <td class="pr-3"><strong>Time</strong></td>
                                        <td>{{ ISODateToMonthNameDayAndHour(bookingBeingPreviewed.start) }} - {{ ISODateToMonthNameDayAndHour(bookingBeingPreviewed.end) }}</td>
                                    </tr>

                                </table>


                    <p class="medium-2 pt-4" v-if="bookingBeingPreviewed.canRemove">Removing this booking does not cancel any charges associated with it.</p>

                    </div>

                </template>
                <template v-slot:footer>
                    <div class="d-flex justify-content-center">
                        <ui-button v-if="bookingBeingPreviewed.canRemove" :working="ui.removeBookingInProgress" class="mr-2" button-type="remove" @click="removeBooking(bookingBeingPreviewed.booking_id)">{{ $trans('Delete') }}</ui-button>
                        <ui-button button-type="cancel" @click="ui.showBookingDetails = false">{{ $trans('Close') }}</ui-button>

                    </div>
                </template>

            </ui-modal>

            <ui-modal :title="$trans('Make booking')"  :open="ui.showBookingModal" @close="ui.showBookingModal = false">
                <template v-slot:body>
                                
                    <div v-if="canUserBookThisResource" class="w-100">
                        <div v-if="ui.bookingModalState == 'select-time'">
                            <div>
                                <label for="description">Reason for booking</label>
                                <input id="description" class="form-control" placeholder="Example: Meeting, interviews, photoshoot ..." type="text" v-model="newBooking.description" />
                            </div>
                            <div class="d-md-flex mt-4">
                                <div class="mr-md-2">
                                    <label for="start">Date</label>
                                    <input v-model="newBooking.start" id="start" type="date" class="form-control form-lg">
                                </div>
                                <div class="mr-md-2">
                                    <label for="time">Start time</label>
                                    <input v-model="newBooking.time" id="time" type="time" class="form-control form-lg">
                                </div>
                                <div class="mr-md-2">
                                    <label for="length">Length</label>

                                    <select class="form-control form-lg" v-model="newBooking.length">
                                        <option v-for="hours in permittedHoursAndHalfHours" :value="hours">{{hours.display}}</option>
                                    </select>
                                </div>
                                <div class="mr-md-2">
                                    <label for="time">End time</label>
                                    <input disabled :value="endHourMinute" type="time" class="form-control form-lg">
                                </div>

                            </div>
                        </div>
                        <div v-if="ui.bookingModalState == 'confirm-booking'">
                            <div v-if="isNewBookingValid" class="mt-3 medium-2 pl-1">
                                <p class="medium-2">You are about to book the <strong>{{resource.name}}</strong></p>

                                <table class="booking-details-table w-100">
                                    <tr>
                                        <td class="pr-3"><strong>Time</strong></td>
                                        <td>{{ ISODateToMonthNameDayAndHour(bookingStart) }} - {{ ISODateToMonthNameDayAndHour(bookingEnd) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="pr-3"><strong>Hours</strong></td>
                                        <td>{{bookingTotalHours}}</td>
                                    </tr>
                                    <tr>
                                        <td class="pr-3"><strong>Fee</strong></td>
                                        <td v-if="resource.price != 0">{{bookingTotalHours}} x {{ resource.price }} = <strong>{{bookingTotalFee}} kr</strong></td>
                                        <td v-else><strong>Free</strong></td>
                                    </tr>

                                </table>
                                <p class="medium-2 mt-3" v-if="resource.price != 0"><strong>Booking fee is not refunded, even if booking is cancelled.</strong></p>
                                <p class="medium-2 mt-3" v-if="resource.price != 0">Please provide a kennitala to be invoiced for this booking:</p>

                                <ui-input-text v-if="resource.price != 0" @blur="ui.showIfKennitalaIsValid = true" :valid="kennitalaCheck.valid" @change="ui.showIfKennitalaIsValid = true" class="form-control-lg kennitala-input"  maxlength="12" v-model="bookingAccountId" />
                                <p v-if="!kennitalaCheck.valid && ui.showIfKennitalaIsValid" class="small not-valid-explainer">Kennitala is not valid</p>
                                <p v-else-if="kennitalaCheck.valid" class="small">Kennitala is valid</p>

                            </div>
                        </div>

                    </div>
                    <div class="h-100 d-flex align-items-center" v-if="!canUserBookThisResource">
                        <p class="large">Sorry, but this resource can only be booked by admins. Contact them if you need to book it.</p>
                    </div>


                </template>

                <template v-slot:footer>
                    <div class="d-flex justify-content-center">
                        <ui-button :disabled="!isNewBookingValid || !canUserBookThisResource" v-if="ui.bookingModalState == 'select-time'" class="mr-2" button-type="action" @click="ui.bookingModalState = 'confirm-booking'">Continue</ui-button>
                        <ui-button :working="bookingInProgress" :disabled="!readyToBook" v-if="ui.bookingModalState == 'confirm-booking'" class="mr-2" button-type="new" @click="book()">Book now</ui-button>
                        <ui-button button-type="cancel" @click="ui.showBookingModal = false; ui.bookingModalState = 'select-time'">Cancel</ui-button>

                    </div>
                </template>
            </ui-modal>

            </div>

            <ui-tabs class="mt-3" v-on:update:section="ui.currentSelection = $event" :tabs="[{display: 'Description', value: 'description'}, {display: 'Calendar', value: 'calendar'}, ]" :current-selection="ui.currentSelection" inline-template>
                <div class="pl-1">
                    <div v-for="tab in tabs" class="tab-wrapper mr-3">
                        <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                            <span v-if="false">
                            <img v-if="currentSelection === tab.value" :src="'/img/checkbox-yellow.svg'">
                                <img v-if="currentSelection !== tab.value" :src="'/img/checkbox.svg'">
                            </span>
                            {{tab.display}}</a>
                    </div>
                </div>

            </ui-tabs>
        </ui-page-header>

        <div class="container-fluid px-md-3 px-0">
            <div class="container">
            <div class="row">
                <div class="col-md-12 mt-4 px-md-3 px-0">

                    <div v-if="ui.currentSelection == 'calendar'">
                        <div class="bg-white p-md-4 px-0">
                            <resource-calendar @event-click="showEvent" @date-click="selectTime" :initial-date="ui.initialCalendarDate" :bookings="resource.bookings"></resource-calendar>
                        </div>

                    </div>

                    <div v-if="ui.currentSelection == 'description'" class="row">
                        <ui-panel class="col-md-7 p-4 bg-white">
                            <ui-img-dynamic :height-ratio="0.4625" class="experiment-image mb-3" v-if="resource.image" :src="resource.image" />
                            <p v-html="resource.description"></p>
                        </ui-panel>
                        <div class="col-md-5">
                            <calendar-pick-day @day-picked="dayPicked" class="bg-white p-3" />
                        </div>
                    </div>

                    <div v-if="ui.currentSelection == 'bookings'">
                        
                        <span v-for="booking in resource.bookings">
                            {{booking.start}}
                        </span>
                    </div>

                </div>
            </div>

        </div>
        </div>

    </div>

</template>

<style scoped>
.not-valid-explainer {
    color:red;
}
.kennitala-input {
    max-width:200px;
}
.booking-details-table tr {
    border-bottom-style:solid;
    border-width:1px;
    border-color:lightgrey;
}
.booking-details-table td {
    padding-bottom:0.3rem;
    padding-top:0.3rem;
}

</style>


<script>
import CalendarPickDay from '../calendar/calendar-pick-day.vue'
import ResourceCalendar from './resource-calendar.vue'
import 'kennitalajs/kennitala'
import backend from '../../backend.js'
export default {
    name: 'resource-details',
    data: function() {
        return {
            ui: {
                showBookingDetails: null,
                showBookingModal: false,
                bookingModalState: 'select-time',
                currentSelection: 'description',
                showIfKennitalaIsValid: false,
                initialCalendarDate: null,
                removeBookingInProgress: false
            },
            bookingErrorMsg: null,
            bookingInProgress: false,
            newBooking: {
                description: null,
                start: null,
                time: '11:00',
                length: {hours: 1, minutes: 0, display: '1 hour'}
            },
            resource: {},
            permittedHoursAndHalfHours: [
                {hours: 1, minutes: 0, display: '1 hour'},
                {hours: 1, minutes: 30, display: '1:30 hours'},
                {hours: 2, minutes: 0, display: '2 hours'},
                {hours: 2, minutes: 30, display: '2:30 hours'},
                {hours: 3, minutes: 0, display: '3 hours'},
                {hours: 3, minutes: 30, display: '3:30 hours'},
                {hours: 4, minutes: 0, display: '4 hours'},
                {hours: 4, minutes: 30, display: '4:30 hours'},
                {hours: 5, minutes: 0, display: '5 hours'},
                {hours: 5, minutes: 30, display: '5:30 hours'},
                {hours: 6, minutes: 0, display: '6 hours'},
                {hours: 6, minutes: 30, display: '6:30 hours'},
                {hours: 7, minutes: 0, display: '7 hours'},
                {hours: 7, minutes: 30, display: '7:30 hours'},
                {hours: 8, minutes: 0, display: '8 hours'},
                {hours: 8, minutes: 30, display: '8:30 hours'},
                {hours: 9, minutes: 0, display: '9 hours'},
                {hours: 9, minutes: 30, display: '9:30 hours'},
                {hours: 10, minutes: 0, display: '10 hours'},
                {hours: 10, minutes: 30, display: '10:30 hours'},
                {hours: 11, minutes: 0, display: '11 hours'},
                {hours: 11, minutes: 30, display: '11:30 hours'},
                {hours: 12, minutes: 0, display: '12 hours'}
            ],
            bookingAccountId: null
        }
    },
    components: {
        ResourceCalendar,
        CalendarPickDay
    },
    watch: {
        bookingAccountId (newVal, oldVal) {
            if (this.kennitala.length >= 10) {
                this.ui.showIfKennitalaIsValid = true
            }
        }
    },
    computed: {
        bookingBeingPreviewed() {
            if (this.ui.showBookingDetails) {
                let details = this.resource.bookings.find(booking => booking.booking_id == this.ui.showBookingDetails)
                details.bookedBy = this.$stores.profiles.getProfilePreviewById(details.user_id)

                details.canRemove = false
                if (this.isUserAdmin) {
                    details.canRemove = true
                } else if (this.$isUserAuthenticated && details.user_id == this.$currentUser.user_id) {
                    details.canRemove = true
                }

                return details
            }
            return null
        },
        readyToBook() {
            if (
                this.isNewBookingValid 
                && this.canUserBookThisResource
                && (this.kennitalaCheck.valid || this.resource.price == 0)
                ) {
                return true
            }
            return false
        },
        kennitalaCheck() {
            if (this.kennitala !== null) {
                return new Kennitala(this.kennitala)                
            }
            return new Kennitala('')
        },
        kennitala () {
            if (this.bookingAccountId == null) {
                return null
            }
            return this.bookingAccountId.replace(/\D/g, '')
        },
        canUserBookThisResource() {
            if (this.isUserAdmin) {
                return true
            }
            if (this.resource.bookable_by.includes('users')) {
                return true
            }
            return false
        },
        isNewBookingValid() {
            return this.newBooking.start && this.newBooking.time
        },
        bookingStart() {
            return this.newBooking.start + ' ' + this.newBooking.time
        },
        bookingEnd() {
            let date = new Date(this.bookingStart)
            date.setHours(date.getHours() + this.newBooking.length.hours)
            date.setMinutes(date.getMinutes() + this.newBooking.length.minutes)
            // format date as timestamp string
            return date.toISOString()
        },
        bookingTotalHours() {
            if (this.isNewBookingValid) {
                const start = new Date(this.bookingStart)
                const end = new Date(this.bookingEnd)
                const diff = end - start
                return diff / (1000 * 60 * 60)

            }
            return null
        },
        bookingTotalFee () {
            if (this.isNewBookingValid) {
                return this.bookingTotalHours * this.resource.price
            }
            return null            
        },
        endHourMinute: function () {
            let date = new Date(this.bookingStart)
            date.setHours(date.getHours() + this.newBooking.length.hours)
            let hoursFormatted = '00'
            if ( date.getHours() < 10) {
                hoursFormatted = '0' + date.getHours()
            } else {
                hoursFormatted = date.getHours()
            }
            date.setMinutes(date.getMinutes() + this.newBooking.length.minutes)
            let minutesFormatted = '00'
            if ( date.getMinutes() < 10) {
                minutesFormatted = '0' + date.getMinutes()
            } else {
                minutesFormatted = date.getMinutes()
            }
            return hoursFormatted+ ':' + minutesFormatted
        }
    },
    props: {
        isUserAdmin: {
            required: false,
            default: false
        },
        resourceId: {
            required: true
        }
    },
    methods: {
        showEvent(event) {
            this.ui.showBookingDetails = event.extendedProps.booking_id
        },
        dayPicked (date) {
            this.ui.initialCalendarDate = date
            this.ui.currentSelection = 'calendar'
        },
        selectTime (event) {
            let time = event.dateStr
            if (!event.allDay) {
                this.newBooking.start = time.split('T')[0]
                let hour = time.split('T')[1]
                this.newBooking.time = hour.split(':')[0] + ':' + hour.split(':')[1]
            } else {
                this.newBooking.start = time
            }
            this.ui.showBookingModal = true
        },
        ISODateToMonthNameDayAndHour (date) {
            return new Date(date).toLocaleString('en-US', {month: 'short', weekday: 'long', hour12: false, day: 'numeric', hour: 'numeric', minute: 'numeric'})
        },
        ISODateToHuman (date) {
            return new Date(date).toLocaleString()
        },
        removeBooking (bookingId) {
            if (!confirm(this.$trans('Are you sure you want to remove this booking?'))) {
                return
            }
 
            this.ui.removeBookingInProgress = true
            backend.delete('/resources/' + this.resourceId + '/bookings/' + bookingId + '/').then((response) => {
                if (response.data.status == 'success') {
                    this.ui.removeBookingInProgress = false
                    this.fetchResource()
                    this.ui.showBookingDetails = null

                    this.$stores.ui.notifySuccess('Booking removed.')
                } else {
                    this.ui.removeBookingInProgress = false
                }
            }).catch((error) => {
                this.ui.removeBookingInProgress = false
            })
        },
        book () {
            this.bookingInProgress = true
            backend.post('/resources/' + this.resourceId + '/bookings/', {
                start: this.bookingStart,
                end: this.bookingEnd,
                description: this.newBooking.description,
                accounting_id: this.kennitala
            }).then((response) => {
                if (response.data.status == 'success') {
                    this.bookingInProgress = false
                    this.fetchResource()
                    this.ui.showBookingModal = false
                    this.ui.bookingModalState = 'select-time'
                } else {
                    this.bookingInProgress = false
                }
            }).catch((error) => {
                this.bookingInProgress = false
                this.ui.bookingModalState = 'select-time'
            })
        },
        fetchResource() {
            backend.get('/resources/' + this.resourceId + '/').then((response) => {
                this.resource = response.data.resource
            })
        }
    },
    mounted () {
        this.fetchResource()
    }    

}

</script>