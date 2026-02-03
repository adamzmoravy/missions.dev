<template>

<div>

    <admin-accounts-custom-invoice v-if="sendCustomInvoice" :custom-invoice-prop="customInvoice" @close="sendCustomInvoice = false" />

    <ui-modal :open="account != null" @close="account = null">

        <template #header>
            <h2>Account</h2>
        </template>

        <template #body>

            <admin-accounts-new-items :account-to-add-to="account" @send-custom-invoice="openSendCustomInvoice" :item-to-add="{ item: 'Lunch club', unit_price: '20000' }" />

        </template>

    </ui-modal>

        <div v-for="registration in registrations" :key="registration.id" class="mb-4">


            <ui-panel class="bg-white">

                <table class="info-table">
                    <tr>
                        <td>Name</td>
                        <td><ui-a :href="'/profile/' + registration.user.user_id">{{ registration.user.first_name }} {{ registration.user.last_name }}</ui-a>                        </td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ registration.user.email }}</td>
                    </tr>
                    <tr v-for="info in registration.info" :key="info.id">
                        <td :title="info.q">{{ info.q }}</td>
                        <td>
                            <a v-if="isKennitala(info.a)" @click="editAccount(info.a, registration.user)">{{ info.a}}</a>
                            <span v-else>{{ info.a }}</span>
                        </td>
                    </tr>
                </table>

            </ui-panel>

        </div>
    
</div>

</template>

<style scoped>

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table td {
    padding: 8px;
    border: 1px solid #ddd;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

</style>

<script>
import adminAccountsCustomInvoice from '../admin/admin-accounts-custom-invoice.vue'
import backend from '../../backend.js'
import AdminAccountsNewItems from '../admin/admin-accounts-new-items.vue'
export default {
    name: 'group-admin',
    data() {
        return {
            sendCustomInvoice: false,
            customInvoice: null,
            registrations: [],
            account: null
        }
    },
    components: {
        AdminAccountsNewItems,
        adminAccountsCustomInvoice
    },
    methods: {
        openSendCustomInvoice(customInvoice) {
            this.customInvoice = {
                record: customInvoice,
                data: {
                    'reference': ''
                }
            }
            this.sendCustomInvoice = true
        },
        isKennitala(value) {
            return new Kennitala(value).valid
        },
        editAccount(kennitala, user) {
            this.account = {
                company_identification: kennitala,
                name: user.first_name + ' ' + user.last_name,
                email: user.email
            }
        },
        getRegistrations() {
            backend.get('/happenings/' + this.group.happening_id + '/registrations/').then(response => {
                this.registrations = response.data.happening.registrations.reverse()
            })
        }
    },
    props: {
        group: {
            type: Object,
            required: true
        }
    },
    mounted() {
        this.getRegistrations()
    }
}

</script>