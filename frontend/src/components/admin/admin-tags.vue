<template>
    <div class="container-fluid">
             <admin-panel-table 
                :id-field="'tag_id'"

                :record-type="'tags'" 
                :fields-meta="fieldsMeta" 
                :table-data="tags" 
                @new-record="createNewTag" 
                @remove-record="removeTag" 
                @save-field="updateTag"

                />

        </div>

</template>

<script>
import backend  from '../../backend.js'
import adminPanelTable from './admin-panel-table.vue'
export default {
    props: {
    },
    components: {
        adminPanelTable
    },
    mounted() {
        this.fetchTags()
    },
    methods: {
        fetchTags() {
            backend.get('/tags/en').then((response) => {
                this.tags = response.data.tags
            })
        },
        createNewTag(data) {

            backend.post('/tags/en', {
                label: data.label,
                types: data.types
            }).then((response) => {
                this.fetchTags()

            })
        },
        removeTag(data) {

            console.log(data)

            backend.delete(`/tags/${data.id}/`).then((response) => {
                this.fetchTags()
            })

        },
        makeTagEditable(tagId) {
            this.tagBeingEditedId = tagId
        },
        updateTag(data) {

            let tagId = this.tags[data.record].id
            let type = this.tags[data.record].type

            backend.put(`/tags/${tagId}/en`, {
                [data.field]: data.value
            }).then((response) => {
                this.fetchTags()
            })

        },
        renderNewTagFields() {
            this.newTagFieldsDisplayed = !this.newTagFieldsDisplayed
        },
        emptyInputFields() {
            this.label = ''
        }
    },
    data() {
        return {
            fieldsMeta: {
                id: {
                    label: 'ID',
                    type: 'text'
                },
                label: {
                    label: 'Label',
                    type: 'text',
                    editable: true
                },
                types: {
                    label: 'Types',
                    type: 'list',
                    editable: true
                }
            },
            tags: [],
            label: '',
            newTagFieldsDisplayed: false,
            editedTags: [],
            tagBeingEditedId: ''
        }
    }
}
</script>

<style lang="css" scoped>
</style>
