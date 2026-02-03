<template>

    <div>
    <ui-tags :tags="relevantTags" :add-more="true" :selected-tags="selectedTags" @deselected="removeTag" @selected="onTagSelected" @custom-tag-created="createCustomTag" />

    </div>

</template>


<script>

    import backend from '../../backend.js'

    export default {
        name: 'profile-tags-edit',
        components: {
        },
        props: {
            tagTypes: {
                required: true,
                type: Array
            },
            tagTypeSet : {
                required: false,
                default: null,
                type: String
            }
        },
        computed: {
            getTypesAndTags: function () {
                return this.$stores.tags.getTypesAndTags(this.$currentUser.tags_with_type)
            },
            selectedTags: function () {

                let tags = this.$currentUser.tags_with_type
 

                let tagIds = tags.map(tag => tag.tag_id)
                return this.$stores.tags.getTagsByIds(tagIds)
            },
            relevantTags: function () {
                return this.$stores.tags.getTagsByTypes(this.tagTypes)
            }
        },
        methods: {
            createCustomTag: function (tagLabel) {

                backend.post('/custom_tags/' + this.$lang, {
                    label: tagLabel,

                }).then(response => {
                    this.onTagSelected(response.data.tag_id)


                }).catch(error => {
                    console.error('Error creating custom tag:', error)
                })

            },
            onTagSelected: function (tagId) {

                backend.post('/users/me/tags/' + tagId + '/', {
                    tag_type: this.tagTypeSet
                }).then(response => {

                    console.log('Tag added:', response.data)
                }).catch(error => {
                    console.error('Error adding tag:', error)
                })

            },
            removeTag: function (tagId) {

                backend.delete('/users/me/tags/' + tagId + '/' + this.tagTypeSet + '/').then(response => {

                    console.log('Tag removed:', response.data)
                }).catch(error => {
                    console.error('Error removing tag:', error)
                })


            }
        }
    }

</script>