<template>
    <span v-if="unseen_events !== 0" v-bind:class="{ 'activity-doc-on': unseen_events !== 0}" class="activity-doc">{{unseen_events}}</span>
</template>

<script>
    import backend from '../../backend.js'
    export default {
        name: 'activity',
        data() {
            return {
                unseen_events: 0
            }
        },
        props: {
        },
        notifications: {
        },
        methods: {
            setup: function() {
                this.fetchUnseenEvents()
            },
            fetchUnseenEvents: function () {
                backend.get('/events/since/my_last_fetched/', ).then(response => {
                    this.unseen_events = response.data.count
                })
            }
        },
        beforeDestroy () {
        },
        created: function() {
            setTimeout((scope) => scope.fetchUnseenEvents(), 60000, this)

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

        }
    }
</script>
