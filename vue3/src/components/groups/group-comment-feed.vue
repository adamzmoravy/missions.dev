<template>

    <div v-if="groupPreview">
        <div class="d-flex flex-row-reverse pr-1">

            <entity :entity="entity" :key="entity.link" class="entity-headline small-entity" />

        </div>
        <comment-single post-format="tiptap-json" :post-reply-url="'/happenings/' + groupPreview.happening_id + '/comment/'" :comment="comment" :key="comment.comment_id" class="mb-3 bg-white" />
    </div>


</template>

<style scoped>

.entity-headline {
    max-width:20rem;
}

</style>

<script>

import commentSingle from '../comments/comment-single.vue'

export default {
    name: 'group-comment-feed',
    data () {
        return {

        }
    },
    components: {
        commentSingle
    },
    methods: {
        subscribeToTopicEvents: function () {

            this.$stores.events.subscribeToChannel(this.$config.platform.platform_key + '-comments-' + this.comment.topic)

        },
    },
    watch: {
        $latestEvent: function(event) {
            let reactToEvents = ['comment-changed']
            if (reactToEvents.includes(event.eventName)) {
                if (event.data.topic === this.comment.topic) {
                    this.$stores.groups.fetchSingleComment(event.data.comment_id)
                }
            }

        }
    },
    created () {
        this.subscribeToTopicEvents()
    },
    computed: {
        entity () {             
            return {
                link: '/group/' + this.groupPreview.happening_id,
                title: this.groupPreview.name,
                type: 'group'
            }
        },
        groupPreview: {
            get () {
                return this.$stores.groups.getGroupPreviewByUUID(this.comment.topic)
            }
        }
    },
    props: {
        comment: {
            type: Object,
            required: true
        }
    },

}

</script>
