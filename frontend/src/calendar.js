import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import moment from 'moment'
moment().format()

export function useCalendar() {

    var timeNow = ref(Date.now())

    let timer
    setInterval(function () { 
        timeNow.value = Date.now()
    }, 60000)

    const eventsData = reactive({
        value: []
    })

    const setEvents = (events) => {
        eventsData.value = events
    }    

    const events = computed(() => {

        let formattedEvents = [];
        eventsData.value.forEach((element) => {

            if (moment(element.start).isBefore(moment(timeNow.value))) {
                element['started'] = true
            } else {
                element['started'] = false
            }

            if (moment(element.end).isBefore(moment(timeNow.value))) {
                element['ended'] = true
            } else {
                element['ended'] = false
            }

            element['timesHuman'] = eventTimesHuman(element)

            element['human'] = moment.duration(element.start).humanize()

            formattedEvents.push(
                element
            )
        })

        return formattedEvents
    })

    const upComingEvents = computed(() => {

        return events.value.filter((event) => { 

            if (!event.started) {
                return true
            }

         })

    })

    const notFinishedEvents = computed(() => {

        return events.value.filter((event) => { 

            if (!event.ended) {
                return true
            }

         })

    })


    const finishedEvents = computed(() => {

        return events.value.filter((event) => { 

            if (event.ended) {
                return true
            }

         })

    })

    let eventTimesHuman = function (event) {

        let end
        let start = moment(event.start).format('dddd, MMMM Do YYYY, HH:mm')

        // if this.eventDetails.start is the same date as this.eventDetails.end
        // then we only need to show the start date
        if (moment(event.start).isSame(event.end, 'day')) {
            end = moment(event.end).format('HH:mm')
        } else {
            end = moment(event.end).format('dddd, MMMM Do YYYY, HH:mm')
        }

        return {
            start: start,
            end: end    
        }

    }

    return {
        setEvents,
        events,
        upComingEvents,
        notFinishedEvents,
        finishedEvents,
        timeNow
    }

}