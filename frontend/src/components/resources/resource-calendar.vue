<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import timeGridPlugin from '@fullcalendar/timegrid';

import { useProfilesStore } from '../../stores/profiles'
import { mapStores } from 'pinia'

export default {
  components: {
    FullCalendar // make the <FullCalendar> tag available
  },
  data() {
    return {
    }
  },
  props: {
    initialDate: {
      type: String,
      required: false,
      default: function () {
        return new Date().toISOString()
      }
    },
    bookings: {
      type: Array,
      required: false,
      default: function () {
        return []
      }
    }
  },
  methods: {
    handleEventClick: function(arg) {
      console.log(arg.event)
      this.$emit('event-click', arg.event)
    },
    handleDateClick: function(arg) {
      console.log(arg)
      this.$emit('date-click', arg)
    },
    getBookingDescription (booking) {
      let desc =  this.getUserName(booking.user_id)

      if (booking.description) {
        desc += ': ' + booking.description
      }
      return desc
    },
    getUserName(userId) {
      let profile = this.profiles.filter(user => {
        return user.user_id.toString() == userId.toString()
      })

      if (profile.length == 0) {
        return 'Unknown'
      }

      return profile[0].first_name + ' ' + profile[0].last_name
    }
  },
  computed: {
    ...mapStores(useProfilesStore),
    calendarOptions() {
        return {
            plugins: [ dayGridPlugin, interactionPlugin, timeGridPlugin],
            initialView: 'timeGridDay',
            initialDate: this.initialDate,            
            allDaySlot: false,
            events: this.events,
            scrollTime: '08:00:00',
            eventClick: this.handleEventClick,
            dateClick: this.handleDateClick,
            timeFormat: 'H(:mm)', // uppercase H for 24-hour clock
            headerToolbar: {
              right: 'prev,next today',
              center: 'title',
              left: 'timeGridDay,timeGridWeek,dayGridMonth'
            }
          }
    },
    profiles: function() {
        return this.$stores.profiles.profilePreviews
    },
    events: function() {
      return this.bookings.map(booking => {
        return {
          title: this.getBookingDescription(booking),
          start: booking.start,
          end: booking.end,
          extendedProps: {
            booking_id: booking.booking_id
          }
        }
      })
    }
  },
  created: function() {
  }
}
</script>
<template>
  <FullCalendar :options="calendarOptions" />
</template>
