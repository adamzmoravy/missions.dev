<template>


        <template v-for="event in events" :key="event.event_id">


            <events-comment class="py-2 menu-item-container-fullwidth" v-if="event.event_type=='comment'" :event="event" />

            <events-new-experiment class="py-2 menu-item-container-fullwidth" v-if="event.event_type=='new_experiment'" :event="event" :key="event.event_id" />

            <events-new-user class="py-2 menu-item-container-fullwidth" v-if="event.event_type=='new_user'" :event="event" :key="event.event_id" />

            <events-comment-happening class="py-2 menu-item-container-fullwidth" v-if="event.event_type=='comment_happening'" :event="event" :key="event.event_id" />

        </template>

        <div v-if="!eventsLoaded" class="loading-screen">
            <img src="../img/loading-dots.svg">
        </div>


</template>

<style scoped>

.loading-screen {
    display:flex;
    justify-content: center;
    align-items: center;
}

</style>

<script>

    import eventsComment from './events-comment.vue'
    import eventsNewExperiment from './events-new-experiment.vue'
    import eventsNewUser from './events-new-user.vue'
    import eventsCommentHappening from './events-comment-happening.vue'

    import backend from '../../backend.js'

    export default {
        name: 'events',
        data() {
            return {
                events: null,
                eventsLoaded: false,
            }
        },
        props: {
            open: {
                required: false,
                type: Boolean,
                default: false
            },
            language: {
                required: false,
                type: String,
                default: function () {
                    return 'en'
                    return this.$lang
                }
            }
        },
        components: {
            eventsComment,
            eventsNewExperiment,
            eventsNewUser,
            eventsCommentHappening
        },
        notifications: {
            errorCommentingNotification: {}
        },
        emits: ['last-activity-fetch'],
        methods: {
            setup: function() {
                this.fetchEvents()
//                this.timer = setInterval(this.fetchEventsPeriodically, 1000)
            },
            lastActivityFetch: function () {
                backend.post('/events/last_activity_fetch/').then((response) => {
                    this.$emit('last-activity-fetch')
                })
            },
            fetchEvents: function () {
                backend.get('/events/').then((response) => {
                    this.events = response.data.events
//                    this.$root.eventBus.$emit('events-fetched')
                    this.eventsLoaded = true
                    this.lastActivityFetch()
                })
            }
        },
        beforeUnmount () {
            this.timer = null
        },
        watch: {
            $latestEvent: function(event) {
                let reactToEvents = ['new_experiment', 'comment', 'new_user', 'comment_happening']
                if (reactToEvents.includes(event.event_type)) {
                    this.setup()
                }

            }
        },
        mounted: function() {
            this.setup()
        }
    }
</script>
