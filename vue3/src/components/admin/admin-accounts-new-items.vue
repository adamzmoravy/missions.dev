<template>

            <div class="w-100">

                <div class="d-flex pt-3 justify-content-end">
                    <ui-button class="mr-2" :working="fetchingAccounts" size="sm" @click="getAllAccounts">Refresh</ui-button>
                    <ui-button v-if="existingAccount" button-type="new" size="sm" @click="$emit('send-custom-invoice', existingAccount);$emit('close')">Invoice</ui-button>

                </div>

                <h3 class="medium-2">Identity ID: {{ accountToAddTo.company_identification }}</h3>

                <div v-if="existingAccount" class="mb-4">
                    <h3 class="medium">Account exists: {{ existingAccount.name }}</h3>
                    <table class="info-table mb-4">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Start</th>
                                <th>End</th>
                            </tr>
                        </thead>
                        <tr v-for="subscription in existingAccount.subscriptions.records">
                            <td>{{ subscription.item }}</td>
                            <td>{{ subscription.start }}</td>
                            <td>{{ subscription.end }}</td>
                        </tr>
                    </table>

                    <h3 class="medium">Add new subscription:</h3>

                    <ui-input-text class="mb-2 w-100" v-model="itemToAddCustom.item" :placeholder="'Item'" />

                    
                    <div class="d-flex justify-content-between">
                        <ui-input-text class="mb-2 w-25" type="date" v-model="itemToAddCustom.start" :placeholder="'Start date'" />

                        <ui-input-text class="mb-2 w-25" v-model="itemToAddCustom.unit_price" :placeholder="'Unit price'" />
                        <ui-input-text class="mb-2 w-25" v-model="itemToAddCustom.units" :placeholder="'Quantity'" />

                    </div>

                    <ui-button size="md" @click="addNewSubscription()" :button-type="'new'" :label="$trans('Add subscription')" class="mr-2" />

                </div>

                <div v-else-if="!fetchingAccounts">

                    <p>No account found with this ID. Would you like to create one?</p>

                    <p>
                        <ui-button size="md" @click="createNewAccount()" :button-type="'new'" :label="$trans('New account')" class="mr-2" />
                    </p>

                </div>
            </div>

</template>

<style scoped>

.info-table, .info-table td, th {
    padding: 4px;
    border: 1px solid #ddd;
} 

.info-table {
    width: 100%;
    border-collapse: collapse;
}

</style>

<script>
import backend from '../../backend.js'
export default {
    name: 'accounts-new-items',
    props: {
        accountToAddTo: Object,
        itemToAdd: Object
    },
    data: function() {
        return {
            fetchingAccounts: false,
            itemToAddCustom:
                {   
                item: '',
                start: '',
                end: '',
                units: 1,
                unit_price: ''
            },
            fieldsMeta: {},
            allAccounts: [],

            account: {
                billing_emails: '',
                name: '',
                company_identification: '',
                spaces: 'Coworking',
                subscriptions: [
                    {
                        item: 'Coworking',
                        start: '',
                        end: '',
                        units: 1,
                        unit_price: '10000'
                    },
                    {
                        item: 'Members',
                        start: '',
                        end: '',
                        units: 1,
                        unit_price: '5000'
                    }
                ]
            },
            start_date: ''
        }
    },
    computed: {
        existingAccount: function() {
            return this.allAccounts.find(account => account.company_identification == this.accountToAddTo.company_identification)
        }
    },
    methods: {
        createNewAccount: function() {
            let account = {
                billing_emails: this.accountToAddTo.email,
                name: this.accountToAddTo.name,
                company_identification: this.accountToAddTo.company_identification,
            }

            backend.post('/accounts/', account).then(response => {
                if (response.data.status == 'success') {
                    this.getAllAccounts()
                }
            })
        },
        getAllAccounts: function() {
            this.fetchingAccounts = true
            backend.get('/accounts/').then((response) => {
                this.allAccounts = response.data.accounts,
                this.fieldsMeta = response.data.fields_meta
                this.fetchingAccounts = false
            })
        },
        addNewSubscription() {

            backend.post('/accounts/' + this.existingAccount.account_id + '/subscriptions/', this.itemToAddCustom).then(response => {
                if (response.data.status == 'success') {
                    this.getAllAccounts()
                }
            })

        },                    
        submit: function() {

            let account = this.account

            account.subscriptions[0].start = this.start_date
            account.subscriptions[1].start = this.start_date

            backend.get('/accounts/', this.account).then(response => {
                if (response.data.status == 'success') {
                    this.$emit('fetch-accounts')
                    this.account.billing_emails = ''
                    this.account.name = ''
                    this.account.company_identification = ''
                    this.start_date = ''
                }
            })

        }
    },
    mounted() {
        this.itemToAddCustom.item = this.itemToAdd.item
        this.itemToAddCustom.unit_price = this.itemToAdd.unit_price
        this.getAllAccounts()
    }
}

</script>