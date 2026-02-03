<template>
    <div class="tags-group">
        <span
            v-for="(tag, index) in tagsOrdered"
            @click="!readonly && tagSelected(tag.id)"
            :key="index"
            class="tag"
            :class="{selected_tag: selectedTagsIds.includes(tag.id), updating: tagsUpdating.includes(tag.id)}"

        >
            {{ tag.label }}

            <span v-if="tag.count && tag.count > 1" class="tag-count">{{ tag.count }}</span>

        </span>
    </div>
    <div v-if="additionalTags.length">
        <p class="mt-2 mb-2">Additional Tags:</p>
        <span v-for="label in additionalTags" class="tag selected_tag">

            {{ label }}

        </span>

    </div>

    <div v-if="addMore && !addingMore" class="pt-3">
        <p>Other? <ui-a @click="addingMore = true" class="underline">Add more</ui-a></p>
    </div>

    <div v-if="addingMore">

        <ui-input-text v-model="newTagLabel" class="newTagInput mt-2"></ui-input-text>

        <ui-button @click="createNewTag" class="mr-2" button-type="new" size="sm">Add</ui-button>
        <ui-button @click="addingMore = false" button-type="cancel" size="sm">Done</ui-button>

    </div>
</template>

<style scoped>
.newTagInput {
    max-width: 300px;
    margin-bottom: 8px;
}
.tags-group {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}
.updating {
    opacity: 0.2;
    pointer-events: none;
    cursor: progress;
}
.tag {
    display: inline-flex;
    align-items: center;

}

.tag:hover .tag-count {

    border-color:white;
}


.tag:hover {
    text-decoration: underline;
    cursor: pointer;
}

.tag-count {
    display: flex;
    align-items: center;
    justify-content: center;
    border-color:var(--very-dark);
    border-style: solid;
    border-width: 1px;
    border-radius: 50%;
    width: 1.75em;
    height: 1.75em;
    font-size: 0.75em;
    margin-left: 4px;
    margin-right:-2px;
}


</style>

<script>

export default {
        name: 'ui-tags',
        data() {
            return {
                tagsUpdating: [],
                selectedTagsIdsLocal: [],
                addingMore: false,
                additionalTags: [],
                newTagLabel: ''
            }
        },
        emits: ['selected', 'deselected', 'custom-tag-created'],
        methods: {
            createNewTag: function () {

                this.$emit('custom-tag-created', this.newTagLabel)

                this.additionalTags.push(this.newTagLabel)
                this.newTagLabel = ''
            },
            tagSelected(tagId) {
                this.tagsUpdating.push(tagId)
                if (this.selectedTagsIds.includes(tagId)) {
                    this.$emit('deselected', tagId)
                } else {
                    this.$emit('selected', tagId)
                }
            } 
        },
        computed: {
            tagsOrdered() {
                if (this.orderBy === 'most-used') {
                    return this.tagsOrderedByCount
                }
                return this.localTags
            },
            selectedTagsIds() {
                return this.selectedTags.map(tag => tag.id)
            },
            tagsOrderedByCount() {
                return [...this.localTags].sort((a, b) => (b.count || 0) - (a.count || 0))
            },
            localTags() {
                if (this.tags.length !== 0) {
                    return this.tags
                }
                else {
                    return this.$stores.tags.getTagsByIds(this.tagIds)
                }
            }
        },
        watch: {
            selectedTags: {
                handler() {
                    this.tagsUpdating = []
                    this.selectedTagsIdsLocal = [...this.selectedTags.map(tag => tag.id)]
                },
                deep: true,
                immediate: true
            }
        },
        props: {
            addMore: {
                type: Boolean,
                default: false
            },
            readonly: {
                type: Boolean,
                default: false
            },
            orderBy: {
                type: String,
                default: 'default' // or 'most-used'
            },
            tags: {
                required: false,
                type: Array,
                default: () => []
            },
            tagIds: {
                required: false,
                type: Array,
                default: () => []
            },
            selectedTags: {
                required: false,
                type: Array, // Array of tag ids
                default: () => []
            }
        }
    }
</script>