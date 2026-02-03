

<template>

    <div class="row">
        <div class="col-12">



            <div class="pl-2">

                Status:

                <label class="ml-3" v-for="status in statuses" :key="status">
                    <input type="checkbox" :value="status" v-model="filtersStatus">
                    {{ status }}
                </label>


            </div>
            <div class="pl-2">

                Years:

                <label class="ml-3" v-for="year in years" :key="year">
                    <input type="checkbox" :value="year" v-model="filtersYear">
                    {{ year }}
                </label>


            </div>


        <table class="table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>

                </tr>
                <tr class="explainer-row">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

                </tr>
            </thead>
            <tbody>
                <tr v-for="(invoices, index) in filteredInvoices" :key="index">
                    <td>{{ invoices.account_name }} ({{ invoices.company_identification }})</td>
                    <td>{{ invoices.amount }}</td>
                    <td>{{ invoices.due.substring(0, 10) }}</td>
                    <td>{{ invoices.status }}</td>
°                </tr>
            </tbody>
        </table>



        </div>            
    </div>

</template>

<style scoped>

.explainer-row td  {
    background-color:rgb(228, 255, 203);
    font-weight: bold;
}

th {
    background-color:rgb(200, 200, 200);
    font-weight: bold;  
    border-top: 2px solid black;
    border-bottom: 2px solid black;
    color: black;
}
    
</style>

<script>

import { filterHash } from '@fullcalendar/core/internal';
import backend from '../../backend.js'
export default {
    name: 'accounts-invoices-overview',
    watch: {

    },
    props: {
        accounts: {
            type: Array,
            required: true
        },

    },
    data: function() {
        return {
            filtersStatus: [
                "SENT"
            ],
            filtersYear: []
        }
    },
    computed: {
        years: function() {
            const yearSet = new Set();
            this.invoices.forEach(invoice => {
                if (invoice.due) {
                    yearSet.add(new Date(invoice.due).getFullYear());
                }
            });
            return Array.from(yearSet).sort((a, b) => b - a);
        },
        statuses: function() {
            const statusSet = new Set();
            this.invoices.forEach(invoice => {
                if (invoice.status) {
                    statusSet.add(invoice.status);
                }
            });
            return Array.from(statusSet);
        },
        filteredInvoices: function() {
            let filtered = this.invoices
            if (this.filtersYear.length > 0) {
                filtered = filtered.filter(invoice => this.filtersYear.includes(new Date(invoice.due).getFullYear()));
            }
            if (this.filtersStatus.length > 0) {
                filtered = filtered.filter(invoice => this.filtersStatus.includes(invoice.status));
            }
            return filtered;
        },
        invoices: function() {
            // Build invoicesArray and add extra properties
            const invoicesArray = [];
            this.accounts.forEach(account => {
                if (account.invoices && typeof account.invoices === 'object') {
                    Object.values(account.invoices).forEach(invoice => {
                        invoicesArray.push({
                            ...invoice,
                            account_name: account.name,
                            company_identification: account.company_identification
                        });
                    });
                }
            });
            return invoicesArray;

        }


    },
    methods: {

    }    
}

</script>