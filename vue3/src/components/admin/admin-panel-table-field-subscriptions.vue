<template>

<div>
    <ui-button class="expand-button" @click="$emit('open-drawer');showModal = !showModal"><span>{{record.subscriptions.records.length}}</span></ui-button>


    <portal :to="'accounts-' + i">
        <h3 class="large">Subscriptions for {{record.name}}</h3>
        <admin-panel-table v-if="showModal"
            class="sub-records"
            record-type="subscriptions"
            @new-record="newSubscription" 
            @remove-record="removeSubscription" 
            @save-field="saveField"
            :table-data="record.subscriptions.records" 
            :fields-meta="record.subscriptions.fields_meta">
        </admin-panel-table>
    </portal>


</div>


</template>

<style scoped>

</style>

<script>
import backend from '../../backend.js'
export default {
    name: 'admin-panel-table-expand-subscriptions',
    props: {
        i: {
            required: true
        },
        record: {
            required: true
        }
    },
    components: {
        
    },
    data: function() {
        return {
            showModal: false,
        }
    },
    methods: {
        removeSubscription: function (record) {
            let subscriptionId = record.subscription_id
            let accountdId = record.account_id
            backend.delete('/accounts/' + record.account_id + '/subscriptions/' + subscriptionId + '/').then((response) => {
                this.$emit('fetch-records')
            })
        },
        newSubscription(data) {
            backend.post('/accounts/' + this.record.account_id + '/subscriptions/', data).then(response => {
                if (response.data.status == 'success') {
                    this.$emit('fetch-records')
                }
            })
        },
        saveField (event) {
            let subscriptionId = this.record.subscriptions.records[event.record].subscription_id
            let accountId = this.record.account_id
            this.patchSubscription(accountId, subscriptionId, {
                [event.field]: event.value
            })
        },
        patchSubscription(accountId, subsriptionId, data) {
            backend.patch('/accounts/' + accountId + '/subscriptions/' + subsriptionId + '/', data).then((response) => {
                this.$emit('fetch-records')
            })
        },
    },
    mounted () {
    }
}

</script>