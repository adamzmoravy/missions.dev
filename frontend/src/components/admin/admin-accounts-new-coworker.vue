<template>

        <ui-modal @close="$emit('close')" :title="'Add coworker'" :open="true" size="">
            <template #body>
                <div class="w-100 pt-2">
                    <ui-input-text class="mb-2 w-100" v-model="account.name" :placeholder="'Name'"></ui-input-text>
                    <ui-input-kennitala class="mb-2 w-50" v-model="account.company_identification" :placeholder="'Kennitala'" />
                    <ui-input-text class="mb-2 w-100" v-model="account.billing_emails" :placeholder="'Billing emails'"></ui-input-text>
                    <ui-input-text class="mb-2 w-25" type="date" v-model="start_date" :placeholder="'Start date'"></ui-input-text>

                </div>
            </template>
            <template #footer>
                <div class="d-flex justify-content-center">
                    <ui-button @click="submit()" :button-type="'action'" :label="$trans('Add')" class="mr-2" />
                    <ui-button @click="$emit('close')" :button-type="'cancel'" :label="$trans('Close')" />
                </div>
            </template> 

        </ui-modal>

</template>

<script>
import backend from '../../backend.js'
export default {
    name: 'accounts-new-coworker',
    data: function() {
        return {
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
                        unit_price: '11000'
                    },
                    {
                        item: 'Members',
                        start: '',
                        end: '',
                        units: 1,
                        unit_price: '5500'
                    }
                ]
            },
            start_date: ''
        }
    },
    methods: {
        submit: function() {

            let account = this.account

            account.subscriptions[0].start = this.start_date
            account.subscriptions[1].start = this.start_date

            backend.post('/accounts/', this.account).then(response => {
                if (response.data.status == 'success') {
                    this.$emit('fetch-accounts')
                    this.account.billing_emails = ''
                    this.account.name = ''
                    this.account.company_identification = ''
                    this.start_date = ''
                }
            })

        }
    }
}

</script>