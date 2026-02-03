<template>
  <FullCalendar :options="calendarOptions"/>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction';

export default {
  components: {
    FullCalendar
  },
  data() {
    return {
      calendarOptions: {
        plugins: [dayGridPlugin, interactionPlugin],
        selectable: true,
        dateClick: this.handleDayClick,
        eventClick: this.handleEventClick,
        events: this.events.map(event => ({ start: event.start }))
      }
    }
  },
  props: {
    events: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    handleEventClick(event) {
      this.$emit('event-clicked', event.event.extendedProps.data)
      this.$emit('event-id-selected', event.event.id)

    },
    handleDayClick(info) {
        this.$emit('day-picked', info.date)
    }
  },
  watch: {
    events: {
      handler(newVal) {
        this.calendarOptions.events = newVal.map(event => ({ start: event.start, data: event, id: event.event_id }));
      },
      deep: true
    }
  }
}
</script>

