<template>


    <admin-panel-table 
        :table-data="spaces" 
        :fields-meta="fieldsMeta"
        @new-record="addNewSpace"                     
        @remove-record="removeSpace"
    />



</template>

<script>

import adminPanelTable from './admin-panel-table.vue'

export default {
    name: 'admin-spaces',
    computed: {
        spaces() {
            return this.$stores.spaces.spaces
        },
        fieldsMeta: function() {
            return {
                id: {
                    type: 'number',
                    label: this.$trans('ID'),
                    sortable: true
                },
                title: {
                    type: 'string',
                    label: this.$trans('Name'),
                    sortable: true,
                    editable: true
                }
            }
        }
    },
    methods: {
        addNewSpace(data) {
            this.$stores.spaces.createNewSpace({
                name: data.title
            })
        },
        removeSpace(data) {
            this.$stores.spaces.removeSpace(data)
        }
    },
    components: {
        adminPanelTable
    }
}

</script>