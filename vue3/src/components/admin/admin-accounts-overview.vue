<template>


    <div class="row">
        <div class="col-12">

            <ui-modal :open="batchEditAction" title="Batch edit" @close="batchEditAction = false">
                <template #body>

                    <div>
                        <label for="batchUnitPrice">Set unit price to:</label>
                        <input class="form-control form-large" id="batchUnitPrice" type="number" v-model="batchUnitPriceChange" />

                    </div>

                </template>
                <template #footer>
                    <div class="d-flex justify-content-center">
                        <ui-button :working="workingOnBatchChange" @click="applyBatchChanges" button-type="new" :disabled="batchEdit.length == 0" class="mr-2">Apply changes</ui-button>
                        <ui-button @click="batchEditAction = false">Close</ui-button>
                    </div>

                </template>
            </ui-modal>



            <div class="pl-2">

                Filters:
                <label class="ml-3">
                    <input type="checkbox" value="past" v-model="filters">
                    Past
                </label>


                <label class="ml-3">
                    <input type="checkbox" value="current" v-model="filters">
                    Current
                </label>

                <label class="ml-3">
                    <input type="checkbox" value="future" v-model="filters">
                    Future
                </label>


                <select v-model="selectedItem" class="ml-3">
                    <option value="">All Items</option>
                    <option v-for="item in subscriptionItems" :key="item" :value="item">
                        {{ item }}
                    </option>
                </select>

            </div>

            <div class="pl-2 pt-2 pb-4">
                <ui-button class="mt-2" size="sm" button-type="new" :disabled="batchEdit.length == 0" @click="batchEditAction = true">Batch edit ({{ batchEdit.length }})</ui-button>
            </div>
        <table class="table" v-if="show == 'subscriptions'">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Unit price</th>
                    <th>Units </th>
                    <th>Total</th>
                    <th>Account</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Edit</th>
                </tr>
                <tr class="explainer-row">
                    <td>{{ filteredSubscriptions.length }} subscriptions</td>
                    <td>{{ mostCommonUnitPrice }} (common)</td>
                    <td>{{ numberOfUnits }}</td>
                    <td>{{ totalMonthly }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ batchEdit.length}} <ui-button size="sm" @click="selectAllSubscriptions">Select all</ui-button></td>
                </tr>
            </thead>
            <tbody>
                <template v-for="(subscription, index) in filteredSubscriptions" :key="index">
                    <tr :class="{ 'just-edited': hasJustBeenEdited.includes(subscription.subscription_id) }">
                    <td>{{ subscription.item }}</td>
                    <td>{{ subscription.unit_price }}</td>
                    <td>{{ subscription.units }}</td>
                    <td>{{ subscription.total_monthly}}</td>
                    <td>{{ subscription.account_name }} ({{ subscription.company_identification }})</td>
                    <td>{{ subscription.start }}</td>
                    <td>{{ subscription.end || 'Ongoing' }}</td>
                    <td><input type='checkbox' v-model='batchEdit' :value='subscription'></td>

                    </tr>
                </template>
            </tbody>
        </table>



        </div>            
    </div>

</template>

<style scoped>

.just-edited {
    background-color: yellow;
    transition: background-color 2s ease;
}

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

td {
    max-width: 200px;
}
    
</style>

<script>

import backend from '../../backend.js'
export default {
    name: 'accounts-overview',
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
            workingOnBatchChange: false,
            hasJustBeenEdited: [],
            batchUnitPriceChange: null,
            batchEditAction: false,
            batchEdit: [],
            show: 'subscriptions',
            filters: [
                "current"
            ],
            selectedItem: ''
        }
    },
    computed: {
        totalMonthly: function() {
            let total = 0
            for (let sub of this.filteredSubscriptions) {
                total += sub.units * sub.unit_price
            }
            return Number(total.toFixed(2))
        },
        numberOfUnits: function() {
            let total = 0
            for (let sub of this.filteredSubscriptions) {
                total += sub.units
            }
            return Number(total.toFixed(2)) 
        },
        mostCommonUnitPrice: function() {
            let priceCounts = {}
            for (let sub of this.filteredSubscriptions) {
                if (sub.unit_price in priceCounts) {
                    priceCounts[sub.unit_price] += 1
                } else {
                    priceCounts[sub.unit_price] = 1
                }
            }
            let mostCommon = null
            let highestCount = 0
            for (let price in priceCounts) {
                if (priceCounts[price] > highestCount) {
                    highestCount = priceCounts[price]
                    mostCommon = price
                }
            }
            return mostCommon
        },
        subscriptionItems: function() {
            let items = []
            for (let sub of this.filteredSubscriptions) {
                if (!items.includes(sub.item)) {
                    items.push(sub.item)
                }
            }
            return items
        },
        filteredSubscriptions: function() {

            let subs = []
            
            if (this.filters.includes("current")) {
                let currentSubs = this.subscriptions.filter(sub => {
                    const today = new Date().toISOString().slice(0, 10)
                    if (!sub.end) {
                        return sub.start <= today
                    }
                    return sub.start <= today && today <= sub.end
                }) 
                subs = [...subs, ...currentSubs]
            }
            if (this.filters.includes("past")) {
                let pastSubs = this.subscriptions.filter(sub => {
                    const today = new Date().toISOString().slice(0, 10)
                    return sub.end && sub.end < today
                }) 
                subs = [...subs, ...pastSubs]
            }
            if (this.filters.includes("future")) {
                let futureSubs = this.subscriptions.filter(sub => {
                    const today = new Date().toISOString().slice(0, 10)
                    return sub.start > today
                }) 
                subs = [...subs, ...futureSubs]
            }

            if (this.filters.length == 0) {
                subs = this.subscriptions
            }

            if (this.selectedItem) {
                subs = subs.filter(sub => sub.item === this.selectedItem)
            }

            return subs
        },
        subscriptions: function() {
            // Add account fields to each subscription
            let subs = []
            for (let account of this.accounts) {
                if (account.subscriptions && account.subscriptions.records) {
                    for (let sub of account.subscriptions.records) {
                        sub.account_name = account.name
                        sub.company_identification = account.company_identification
                        sub.spaces = account.spaces
                        subs.push(sub)
                    }
                }
            }
            return subs

        }
    },
    methods: {
        updateSubscription: async function(subscription, field, value) {
            await backend.patch('/accounts/' + subscription.account_id + '/subscriptions/' + subscription.subscription_id + '/', {
            [field]: String(value)
            }).then(response => {
                this.hasJustBeenEdited.push(subscription.subscription_id)
                this.applyBatchChanges()
                return true
            })
            .catch(error => {
                console.error('Error updating subscription:', error)
                throw error
            })
        },
        selectAllSubscriptions: function() {
            this.batchEdit = this.filteredSubscriptions.map(sub => ({ ...sub }))
        },
        applyBatchChanges: function() {
            if (!this.batchUnitPriceChange) {
                alert('Please enter a unit price to set')
                return
            }
            
            this.workingOnBatchChange = true

            if (this.batchEdit.length > 0) {
                
                this.updateSubscription(this.batchEdit[0], 'unit_price', this.batchUnitPriceChange)

                // Remove the first subscription from batchEdit after updating
                this.batchEdit.shift()


            } else {
                this.workingOnBatchChange = false
                this.batchEditAction = false
                this.batchUnitPriceChange = null
                this.$emit('fetch-accounts')
            }
        }            

    }    
}

</script>