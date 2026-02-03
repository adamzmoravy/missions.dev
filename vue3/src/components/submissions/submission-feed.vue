<template>
    

<div>
    <div class="d-flex flex-row-reverse pr-1 entity-label-feed">

        <entity :entity="entity" :key="entity.link" />

    </div>

    <submission-preview-feed :desc-char-limit="6000" :experiment="experiment" />
    <div class="position-relative" v-show="numComments > 0">
        <comment-topic
            @comments-update="commentsUpdate"
            class="feed-comments mb-2"
            :limit="2"
            order="asc"
            :auto-fetch-comments-periodically="false"
            :load-more-on-scroll="false"
            :allow-replies="false"
            :add-comment-editor-open="false"
            :topic="experiment.uuid"
            :fetch-comments-url="'/experiments/' + experiment.experiment_id + '/topic/'"
            :post-reply-url="'/experiments/' + experiment.experiment_id + '/comment/'"
            :post-comment-url="'/experiments/' + experiment.experiment_id + '/topic/'"
            :post-explainer="$trans('comments_help_1')"
            :experiment-id="experiment.experiment_id"
            @view-more-comments="viewMoreComments"
            >
        </comment-topic>
        <ui-button :href="'/submission/' + experiment.experiment_id" :size="'md'" class="mb-3" button-type="default">{{ $trans('View submission') }}</ui-button>
    </div>

</div>


</template>

<style scoped>



.comment-container .entity-label-feed {
    margin-right:10px;
}

.feed-container:hover {
    transform:translateY(-2px);
    filter: drop-shadow(0 0 1.2rem rgb(214, 213, 213));
}

</style>

<script>

import submissionPreviewFeed from './submission-preview-feed.vue'
export default {
    name: 'submission-feed',
    data () {
        return {
            numComments: 0
        }
    },
    components: {
        submissionPreviewFeed
    },
    methods: {
        viewMoreComments () {
            this.$router.push('/' + this.$lang + '/submission/' + this.experiment.experiment_id)
        },
        commentsUpdate (comments) {
            if (comments) {
                this.numComments = comments.length
            }
            this.numComments = 0

        }
    },
    computed: {
        entity () {
            return {
                link: '/' + this.$ui.stagesIndexPages[this.experiment.stage] + '/',
                title: this.$trans(this.$ui.stageHandles[this.experiment.stage]).toUpperCase(),
                type: 'submission',
                data: {
                    stage: this.experiment.stage
                }
            }
        },
        comments: function () {
            this.$refs.comments.comments
        }
    },
    props: {
        experiment: {
            type: Object,
            required: true
        }
    },

}

</script>
