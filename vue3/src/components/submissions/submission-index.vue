<template>
    <ui-cards-container>
        <div class="row">
            <div class="col-lg-12" v-if="tagsInUse.length > 0">
                <ui-tags :tags="tagsInUse" @selected="tagSelected" :selected-tags="selectedTags" @deselected="removeTag" :order-by="'most-used'" class="mb-3"></ui-tags>
            </div>

            <template v-for="submission in submissionsFiltered">
                <submission-preview v-if="submissions.length > 0" :key="submission.data.experiment_id" :experiment="submission.data" />
            </template>
            <div class="col-lg-12" v-if="submissions.length == 0"></div>
        </div>
    </ui-cards-container>
</template>

<script>

import submissionPreview from './submission-preview.vue'
export default {
    name: 'submission-index',
    data () {
        return {
            selectedTagsIds: []
        }
    },
    props: {
        submissions: {
            type: Array,
            default: () => []
        }
    },
    methods: {
        tagSelected(tagId) {
            if (!this.selectedTagsIds.includes(tagId)) {
                this.selectedTagsIds.push(tagId)
            }
        },
        removeTag(tagId) {
            this.selectedTagsIds = this.selectedTagsIds.filter(id => id !== tagId)
        }
    },
    computed: {
        submissionsFiltered() {
            if (this.selectedTagsIds.length === 0) {
                return this.submissions
            }
            return this.submissions.filter(submission => {
                const submissionTagIds = submission.data.tags.map(tag => tag.id)
                return this.selectedTagsIds.every(tagId => submissionTagIds.includes(tagId))
            })
        },
        selectedTags() {
            return this.$stores.tags.getTagsByIds(this.selectedTagsIds)
        },
        tagsInUse() {
            const tagCountMap = {}
            this.submissions.forEach(submission => {
                submission.data.tags.forEach(tag => {
                    if (!tagCountMap[tag.id]) {
                        // Create a shallow copy to avoid mutating original tag
                        tagCountMap[tag.id] = { ...tag, count: 1 }
                    } else {
                        tagCountMap[tag.id].count++
                    }
                })
            })
            return Object.values(tagCountMap)
        },
    },
    components: {
        submissionPreview
    }
}

</script>