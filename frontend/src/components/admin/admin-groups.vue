<template>

        <div>
            <admin-panel-table :table-data="groups" :fields-meta="fieldsMeta"
            
                @new-record="createNewGroup" 
                @remove-record="removeTag" 
                @save-field="updateGroup"
                @visit-group="visitGroup"
                :actions="[
                    {
                        event: 'visit-group',
                        label: 'View group'
                    }
                    ]"            
            />
        </div>


</template>

<script>

import adminPanelTable from './admin-panel-table.vue'

export default {

    name: 'admin-groups',
    computed: {
        groups() {
            return this.$stores.groups.groups
        },
        fieldsMeta: function() {
            return {
                happening_id: {
                    type: 'number',
                    label: this.$trans('ID'),
                    sortable: true
                },
                name: {
                    type: 'string',
                    label: this.$trans('Name'),
                    sortable: true,
                    editable: true
                },
                registrations_count: {
                    type: 'number',
                    label: this.$trans('Members'),
                    sortable: true
                }
            }
        }
    }, 
    methods: {
        visitGroup(data) {
            this.$router.push({ name: 'group', params: { groupId: data.happening_id } })
        },
        createNewGroup(data) {
            let group = {
                name: data.name
            }
            this.$stores.groups.createGroup(group)
        },
        removeGroup(data) {
            this.$stores.groups.removeGroup(data)
        },
        updateGroup(data) {
            
            let config = {
                name: data.value
            }

            this.$stores.groups.updateGroup(config, data.recordValue.happening_id)
        }
    },
    components: {
        adminPanelTable
    }
}

</script>