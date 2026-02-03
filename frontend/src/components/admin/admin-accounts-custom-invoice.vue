<template>

    <ui-modal :title="'Send custom invoice'" :open="true" size="tall" @close="$emit('closed')">

        <template #body>
            <div class="w-100" v-if="customInvoice">
                <h3 class="medium-2">To: {{ customInvoice.record.name }}</h3>
                <ui-input-text class="mb-2 w-25" v-model="customInvoice.data.reference" :placeholder="'Invoice reference'"></ui-input-text>
                <ui-textarea class="mb-2" v-model="customInvoice.data.description" :placeholder="'Invoice description'"></ui-textarea>
                <label class="d-block">Due days</label>
                <input class="mb-2 form-control w-25" v-model="customInvoice.data.due_days" type="number" placeholder="Due days">
                <label class="d-block">Lines</label>

                <div v-for="(line, key) in customInvoice.data.lines" class="mb-2 p-3 d-flex  border invoice-lines">

                    <div>
                        <label>Description</label>
                        <input class="mr-2" type="text" placeholder="Line description" v-model="customInvoice.data.lines[key].description">
                    </div>
                    <div>
                        <label>Units</label>
                        <input class="mr-2" type="number" v-model="customInvoice.data.lines[key].units">
                    </div>

                    <div>
                        <label>Amount</label>
                        <input type="number" v-model="customInvoice.data.lines[key].amount">
                    </div>

                </div>
                <ui-button size="sm" @click="addCustomInvoiceLine()">Add line</ui-button>
            </div>


        </template>

        <template #footer>

            <div class="d-flex justify-content-center">

                <ui-button @click="sendCustomInvoice()" :working="sendingInvoiceNow" :button-type="'action'" :label="$trans('Send')" class="mr-2" />
                <ui-button @click="$emit('close')" :button-type="'cancel'" :label="$trans('Cancel')" />

            </div>

        </template>
    </ui-modal>

</template>


<script>

import backend from '../../backend.js'

export default {
    name: 'admin-accounts-custom-invoice',
    data() {
        return {
            customInvoice: null,
            sendingInvoiceNow: false
        }
    },
    props: {
        customInvoiceProp: Object
    },
    methods: {
        addCustomInvoiceLine () {
            this.customInvoice.data.lines.push({
                "description": '',
                "units": 1,
                "amount": 0,
                "discount_percentage": 0
            })
        },
        createCustomInvoice (record) {
            this.customInvoice = {
                data: {
                    reference: '',
                    description: '',
                    due_days: 14,
                    lines: []
                },
                record: record
            }
            this.addCustomInvoiceLine()
        },
        sendCustomInvoice () {
            this.sendingInvoiceNow = true
            let accountId = this.customInvoice.record.account_id
            backend.post('/accounts/' + accountId + '/custom_invoices/', 
                this.customInvoice.data
            ).then(response => {
                this.sendingInvoiceNow = false
                this.createCustomInvoice(this.customInvoice.record)
                this.$emit('fetch-accounts')
                this.$emit('close')
            })

        }
    },
    mounted() {
        this.createCustomInvoice()
        this.customInvoice.record = this.customInvoiceProp.record
    }
}

</script>