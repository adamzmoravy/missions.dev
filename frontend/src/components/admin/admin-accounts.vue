<template>

    <div>
        <ui-tabs class="mt-3 mb-0 ml-5" v-on:update:section="ui.showSection = $event" :tabs="[{display: 'Administer', value: 'edit'}, {display: 'Monthly invoicing', value: 'invoice'}, {display: 'Accounts', value: 'accounts-overview'}]" :current-selection="ui.showSection" inline-template>
            <div class="pl-1">
                <div v-for="tab in tabs" class="tab-wrapper mr-3">
                    <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                        <span v-if="false">
                        <img v-if="currentSelection === tab.value" :src="'/img/checkbox-yellow.svg'">
                            <img v-if="currentSelection !== tab.value" :src="'/img/checkbox.svg'">
                        </span>
                        {{tab.display}}</a>
                </div>
            </div>
        </ui-tabs>
        <hr class="mt-0">




        

        <ui-modal :title="'Send custom invoice'" :open="customInvoice != null" size="tall" @close="customInvoice = null">

            <template #body>
                <div class="w-100">
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

                    <ui-button @click="sendCustomInvoice()" :button-type="'action'" :label="$trans('Send')" class="mr-2" />
                    <ui-button @click="customInvoice = null" :button-type="'cancel'" :label="$trans('Cancel')" />

                </div>

            </template>

        </ui-modal> 

        <admin-accounts-new-coworker v-if="ui.addCoworkerModal" @fetch-accounts="fetchAccounts" @close="ui.addCoworkerModal = false" />


        <div v-if="ui.showSection == 'operate'" class="container">

            <admin-accounts-operate-new :platform-config="platformConfig" />

        </div>

        <div v-if="ui.showSection == 'edit'">

        
        <admin-panel-table class="pt-3" 
            record-type="accounts"
            @fetch-records="fetchAccounts" 
            @remove-record="removeAccount" 
            @new-record="newAccount" 
            @save-field="saveField"
            @custom-invoice="createCustomInvoice"
            :id-field="'account_id'"
            :table-data="accounts" 
            :fields-meta="fieldsMeta"
            :actions="[
                {
                    event: 'custom-invoice',
                    label: 'Invoice'
                }
                ]"
            >
            <template v-slot:action-buttons>
                <ui-button class="mr-2" button-type="new" size="md" @click="ui.addCoworkerModal = true">Add coworker</ui-button>
                <ui-button class="mr-2" button-type="action" size="md" @click="importFromExcel">Import from Excel</ui-button>
                <input
                    ref="excelFileInput"
                    type="file"
                    accept=".xlsx,.xls"
                    style="display: none"
                    @change="excelFile = $event.target.files[0]"
                >
            </template>
            <template v-slot:filter-buttons>
            </template>
            </admin-panel-table>
        </div>


        <admin-accounts-invoicing class="ml-5" v-if="ui.showSection == 'invoice'" />
        <admin-accounts-overview class="ml-5" @fetch-accounts="fetchAccounts" :accounts="accounts" v-if="ui.showSection == 'accounts-overview'" />
        <admin-invoices-overview class="ml-5" @fetch-accounts="fetchAccounts" :accounts="accounts" v-if="ui.showSection == 'invoices-overview'" />


    </div>

</template>

<style scoped>

.invoice-lines label {
    display:block;
}

</style>

<script>

import backend from '../../backend.js'

import AdminPanelTable from './admin-panel-table.vue'
import AdminAccountsNewCoworker from './admin-accounts-new-coworker.vue'
import AdminAccountsInvoicing from './admin-accounts-invoicing.vue'
import AdminAccountsOverview from './admin-accounts-overview.vue'
import AdminInvoicesOverview from './admin-invoices-overview.vue'
import AdminAccountsOperateNew from './admin-accounts-operate-new.vue'

export default {
    name: 'admin-accounts',
    data: function() {
        return {
            excelFile: null,
            ui: {
                showSection: 'edit',
                addCoworkerModal: false
            },
            customInvoice: null,
            accounts: [],
            fieldsMeta: {},
            platformConfig: []
        }
    },
    components: {
        AdminPanelTable,
        AdminAccountsNewCoworker,
        AdminAccountsInvoicing,
        AdminAccountsOverview,
        AdminInvoicesOverview,
        AdminAccountsOperateNew
    },
    watch: {
        excelFile (newFile) {
            if (!newFile) {
                return
            }
            let formData = new FormData()
            formData.append('file', newFile)
            backend.postFile('/accounts/import_accounts/', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                this.excelFile = null
                this.fetchAccounts()
            })
        }
    },
    methods: {
        fetchPlatformConfig() {

            backend.get('/platform/admin/').then((response) => {
                this.platformConfig = response.data.platform

            })

        },
        importFromExcel () {
            this.$refs.excelFileInput.click()
            if (!this.excelFile) {
                return
            }
        },
        saveField (event) {
            let accountId = this.accounts[event.record].account_id
            this.patchAccount(accountId, {
                [event.field]: event.value
            })
        },
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
            let accountId = this.customInvoice.record.account_id
            backend.post('/accounts/' + accountId + '/custom_invoices/', 
                this.customInvoice.data
            ).then(response => {
                this.customInvoice = null
                this.fetchAccounts()
            })

        },
        removeAccount (record) {
            let accountId = record.account_id
            backend.delete('/accounts/' + accountId + '/').then((response) => {
                this.fetchAccounts()
            })                        
        },
        newAccount(data) {
            backend.post('/accounts/', data).then(response => {
                if (response.data.status == 'success') {
                    this.fetchAccounts()
                }
            })
        },
        patchAccount(accountId, data) {
            backend.patch('/accounts/' + accountId + '/', data).then((response) => {
                this.fetchAccounts()
            }).catch((error) => {
                console.error('Error patching account:', error)
            })
        },
        jiggly () {
            console.log('jiggly')
            this.accounts = []

        },
        fetchAccounts() {
            backend.get('/accounts/').then((response) => {
                this.accounts = response.data.accounts,
                this.fieldsMeta = response.data.fields_meta
            })
        }
    },
    mounted () {
        this.fetchPlatformConfig()
        this.fetchAccounts()
    }
}

</script>