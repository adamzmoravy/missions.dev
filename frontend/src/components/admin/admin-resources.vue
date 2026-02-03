<template>

    <div>
        <admin-panel-table 
            class="mt-4"
            record-type="resources"
            @fetch-records="fetchResources" 
            @remove-record="removeResource" 
            @new-record="newResource"
            @save-field="saveField" 
            :table-data="resources" 
            :fields-meta="fieldsMeta">
        </admin-panel-table>
    </div>

</template>

<script>


import backend from '../../backend.js'
export default {
    name: 'admin-resources',
    data: function() {
        return {
            resources: [],
            fieldsMeta: {}
        }
    },
    methods: {
        saveField (event) {
            let resourceId = this.resources[event.record].resource_id
            this.patchResource(resourceId, {
                [event.field]: event.value
            })
        },
        removeResource (record) {
            let resourceId = record.resource_id
            backend.delete('/resources/' + resourceId + '/').then((response) => {
                this.fetchResources()
            })                        
        },
        newResource(data) {
            backend.post('/resources/', data).then(response => {
                if (response.data.status == 'success') {
                    this.fetchResources()
                }
            })
        },
        patchResource(resourceId, data) {
            backend.patch('/resources/' + resourceId + '/', data).then((response) => {
                this.fetchResources()
            })
        },
        fetchResources() {
            backend.get('/resources/type/all/').then((response) => {
                this.resources = response.data.resources,
                this.fieldsMeta = response.data.fields_meta
            })
        }
    },
    mounted () {
        this.fetchResources()
    }
}

</script>