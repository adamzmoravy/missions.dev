

<template>

    <div>

        <ui-modal :title="'Invoice view'" :open="invoicePreviewShow" @close="invoicePreviewShow = false">
            <template #body>


                <div class="w-100">
                    <table class="w-100 preview-table">

                        <tbody>
                            <tr>
                                <td>{{invoicePreview.account}}</td>
                            </tr>
                            <tr>
                                <td><strong>Description</strong></td>
                                <td>{{invoicePreview.description}}</td>
                            </tr>
                            <tr>
                                <td><strong>Date:</strong></td>
                                <td>{{invoicePreview.invoiceDate}}</td>
                            </tr>
                            <tr>
                                <td><strong>Due:</strong></td>
                                <td>{{invoicePreview.finalDueDate}}</td>
                            </tr>
                        </tbody>

                    </table>



                    <table cellpadding="0"  cellspacing="0" class="w-100 preview-table mb-4 mt-4">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(line, i) in invoicePreview.lines" :key="i">
                                <td> 

                                    <input type="checkbox" class="mr-1 d-inline" v-model="invoicePreviewLines" :value="line" :id="'line-'+i" name="line-select"> 
                                    <label :for="'line-'+i" class="d-inline">{{line.description}}</label>
                                </td>
                                <td>{{line.quantity}}</td>
                                <td>{{line.unitPriceExcludingVat}} {{ invoicePreview.currencyCode }}</td>
                                <td>{{(line.quantity * line.unitPriceExcludingVat)}} {{ invoicePreview.currencyCode }}</td>
                            </tr>
                        </tbody>
                    
                    </table>
                    



                </div>

            </template>

            <template #footer>

                <div class="d-flex justify-content-center">
                    <ui-button :working="sendingCustomInvoiceNow" @click="sendCustomInvoice" class="mr-2">Send</ui-button>
                    <ui-button @click="invoicePreviewShow = false" :button-type="'cancel'" :label="$trans('Close')" />

                </div>
            </template>
        </ui-modal>

    
        <div class="d-flex ">
            <div>
                <label for="monthToInvoice">Month to invoice</label>
                <input id="monthToInvoice" type="month" v-model="monthToInvoice">
            </div>
            <div class="d-inline-block ml-5" v-if="monthToInvoice">
                <label for="monthToInvoice">Check updates</label>
                <ui-button class="mt-auto" size="sm" :working="fetchingAndSyncingNewMonth" @click="fetchAccountsAndSync()">Sync invoices</ui-button>
            </div>

        </div>

        <div class="position-relative" style="min-height:400px;">
            <div v-if="fetchingNewMonth" class="fetching-overlay">
               Loading ...
            </div>
            <table class="table-hover mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Account</th>
                        <th>Kennitala</th>
                        <th>Owed</th>
                        <th>Invoiced</th>
                        <th v-if="false">To invoice</th>
                        <th>Paid</th>

                        <th>Action</th>
                    </tr>
                </thead>
                <tr v-for="account in accounts">
                    <td>{{account.account_id}}</td>
                    <td>{{account.name}}</td>
                    <td>{{account.company_identification}}</td>
                    <td>{{parseInt(account.owed_period).toLocaleString('is-IS')}}</td>
                    <td :class="{warning: account.balance_period < 0}">{{account.invoiced_period.toLocaleString('is-IS')}}</td>
                    <td v-if="false" :class="{warning: account.balance_period < 0}">{{account.balance_period.toLocaleString('is-IS')}}</td>
                    <td :class="{warning: account.invoiced_period > account.paid_period}">{{account.paid_period.toLocaleString('is-IS')}}</td>

                    <td>
                        
                        <ui-button 
                            size="sm" 
                            button-type="new" 
                            :working="sendingInvoices.includes(account.account_id)"
                            :disabled="disableAccountIds.includes(account.account_id)"
                            v-if="account.invoiced_period == 0 && account.owed_period > 0" 
                            class="btn btn-primary d-inline-block mr-2" 
                            @click="invoiceAccount(account.account_id)">
                            Invoice
                        </ui-button>

                        <ui-button 
                            v-if="account.invoiced_period != 0 && account.balance_period < 0"
                            size="sm" 
                            class="btn btn-primary d-inline-block" 
                            @click="previewInvoice(account.account_id)">
                            Partial
                        </ui-button>
                    </td>

                </tr>

            </table>
        </div>

    </div>

</template>

<style scoped>

    .fetching-overlay {
        position: absolute;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 10;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .preview-table td {
        padding:0.4em;
        border-style:solid;
        border-color:#dee2e6;
    }   

    th {
        padding-left:1rem;
        padding-right:1rem;
        padding-bottom:1rem;
    }
    td {
        padding: 1rem;
        border-style:solid;
        border-color:#dee2e6;
    }
    table {
        width:100%;

    }
    label {
        display: block;
    }
    .warning {
        background-color: #f8d7da;
    }
</style>

<script>

import backend from '../../backend.js'
export default {
    name: 'accounts-invoicing',
    watch: {
        monthToInvoice: function() {
            this.fetchingNewMonth = true
            this.fetchAccounts()
            this.disableAccountIds = []
        }
    },
    data: function() {
        return {
            fetchingAndSyncingNewMonth: false,
            fetchingNewMonth: false,
            invoicePreview: null,
            invoicePreviewLines: [],
            invoicePreviewShow: false,
            sendingCustomInvoiceNow: false,
            syncingInvoices: false,
            sendingInvoices: [],
            monthToInvoice: null,
            accounts: [],
            fieldsMeta: {},
            disableAccountIds: []
        }
    },
    computed: {
        monthToInvoiceHuman () {
            let date = new Date(this.monthToInvoice)
            return date.toLocaleString('is', { month: 'short'     })

        },
        customInvoiceData: function() {
            if (!this.invoicePreview) {
                return null
            }

            let data = {
                description: this.invoicePreview.description,
                due_days: 7,
                reference: this.invoicePreview.reference,
                lines: this.invoicePreviewLines.map(line => {
                    return {
                        description: line.description,
                        units: line.quantity,
                        amount: line.unitPriceExcludingVat,
                        discount_percentage: 0
                    }
                })
            }

            return data
        }
    },
    methods: {
        sendCustomInvoice () {
            let accountId = this.invoicePreview.accountId
            this.sendingCustomInvoiceNow = true
            backend.post('/accounts/' + accountId + '/custom_invoices/', 
                this.customInvoiceData
            ).then(response => {
                this.sendingCustomInvoiceNow = false
                this.fetchAccounts()
                this.invoicePreviewShow = false
            })

        },
        syncInvoices: function () {
            this.syncingInvoices = true

            let startDate = new Date()
            startDate.setMonth(startDate.getMonth() - 12)
            startDate = startDate.toISOString().slice(0, 10)

            let endDate = new Date()
            endDate.setMonth(endDate.getMonth() + 1)
            endDate = endDate.toISOString().slice(0, 10)

            backend.get('/accounts/invoices/' + startDate + '/' + endDate + '/')
                .then(response => {
                    this.syncingInvoices = false
                    this.fetchAccounts()
                })
        },
        invoiceAccount: function(accountId) {
            this.sendingInvoices.push(accountId)
            this.disableAccountIds.push(accountId)
            backend.post('/accounts/' + accountId + '/invoices/', {
                month: this.monthToInvoice
            }).then(response => {
                this.fetchAccounts()
                // remove accountId from the sendingInvoices array
                this.sendingInvoices = this.sendingInvoices.filter(id => id != accountId)
            })
        },
        previewInvoice: function(accountId) {
            backend.get('/accounts/' + accountId + '/invoices/' + this.monthToInvoice + '/')
            .then(response => {
                this.invoicePreview = response.data.invoice_data,
                this.invoicePreview.accountId = accountId
                this.invoicePreviewShow = true
            })
        },
        fetchAccounts() {
            if (this.monthToInvoice) 
            backend.get('/accounts/invoiced/' + this.monthToInvoice + '/').then((response) => {
                this.accounts = response.data.accounts
                this.fetchingNewMonth = false
            })
        },
        fetchAccountsAndSync() {
            if (this.monthToInvoice) 
            console.log('syncing for month', this.monthToInvoice)
            this.fetchingAndSyncingNewMonth = true
            backend.get('/accounts/updated/' + this.monthToInvoice + '/').then((response) => {
                this.accounts = response.data.accounts
                this.fetchingAndSyncingNewMonth = false
            })
        }
    }    
}

</script>