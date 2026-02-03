<template>

    <div class="container-fluid">

        <div class="d-flex justify-content-between mt-3 mb-4">
            <div class="action-buttons d-flex">
                <div><slot name="action-buttons"></slot></div>
                    <ui-button size="md" button-type="action">Download as excel</ui-button>                   

            </div>
            <div class="filters">
                <slot name="filter-buttons"></slot>
            </div>
        </div>
        <span class="small p-2 mb-5 d-none">Double click field to edit. ENTER to save, ESC to cancel.</span>
        <table class="table-hover w-100" @keydown.esc="cancelEditFieldMode()" >
            <thead>
                <th v-for="(field, key) in fieldsMeta" class="admin-table-header sortable"
                    :key="field[idField]"
                    v-bind:class="{sortable: true, active: key === activeField}"
                    @click="changeSortField({name: key, type: field.type})">
                    <div>
                        <span :title="key">{{$trans(field.label)}} </span>
                            <span v-if="key === activeField">
                                <span v-if="sortField.direction === 'asc'">
                                    ▼
                                </span>
                                <span v-else="sortField.direction === 'desc'">
                                    ▲
                                </span>
                            </span>
                    </div>
                </th>
                <th>Action</th>
            </thead>
            <tr class="new-record" v-if="offerNewRecord">
                <td class="record-field border" v-for="(field, key) in fieldsMeta">
                    <ui-input-kennitala v-if="field.type == 'kennitala' && field.editable" v-tooltip="field.description" v-model="newRecord[key]" @keydown-enter="submitNewRecord()" />      

                    <template v-else-if="field.type == 'list' && field.editable">
                        <ui-input-list v-tooltip="field.description" v-model="newRecord[key]" class="mb-2" />

                    </template>

                    <ui-input-text :list="'suggestions-' +  key" v-else-if="field.editable" :class="field.type" :type="field.type" v-tooltip="field.description" v-model="newRecord[key]" @keydown-enter="submitNewRecord()" />
                    <datalist :id="'suggestions-' +  key" v-if="field.suggestions">
                        <option v-for="suggestion in field.suggestions" :value="suggestion.value">{{ suggestion.label }}</option>
                    </datalist>
                </td>
                <td class="record-field border"><ui-button button-type="new" size="small" :disabled="!canAddNewRecord" @click="submitNewRecord()">Add</ui-button></td>
            </tr>

            <template v-for="(record, i) in displayedData">
                <tr :class="{ dropshadow: dropDownRecord.i == i }">

                    <template v-for="(field, key) in fieldsMeta">

                        <td v-if="field.component == 'subscriptions'">
                            <div>

                                <admin-panel-table-field-subscriptions 
                                    :i="i"  
                                    :record="record" 
                                    @fetch-records="$emit('fetch-records')">
                                </admin-panel-table-field-subscriptions>

                            </div>                            
                        </td>
                        <admin-panel-table-field v-else @update-field="saveEditField(key, i, $event)" :record-key="key" :field="field" :value="record[key]"></admin-panel-table-field>

                    </template>


                    <td class="record-field border">
                        <ui-button v-for="action in actions" @click="$emit(action.event, record)" size="small" class="mr-2">{{ action.label }}</ui-button>
                        <ui-button v-if="offerRemoveRecord" @click="removeRecord(record)" size="small" button-type="cancel">Remove</ui-button>
                    
                    </td>

                </tr>
                <tr class="dropdown-area visible-dropdown" :class="{'visible-dropdown': dropDownRecord.i==i}">
                    <td colspan="100" class="pr-5 pl-4">   
                        <div class="expandable" :class="{expanded: dropDownRecord.i==i}">
                            <div class="pb-5 pt-4">
                                <portal-target :name="recordType + '-' + i">
                                </portal-target>
                            </div>
                        </div>
                    </td>
                </tr>
            </template>
            <tr>
                <td class="record-field totals border" v-for="(field, key) in fieldsMeta">
                    <span v-if="field.type == 'number'">
                        {{getTotalForField(key).toLocaleString('is-IS')}}
                    </span>
                </td>
            </tr>
        </table>        

    </div>

</template>


<style>
.expand-button {
        min-width:100px;
    }
</style>

<style>
    .totals {
        font-weight: 600;
    }
    input {
        font-weight:100;
    }

    .number {
        width:70px;
    }
    .dropdown-area {
        opacity:0;
        background-color: rgba(76, 76, 76);
        box-shadow:0px 17px 10px -15px rgba(144, 86, 86, 0.44) inset;
        color:white;
    }

    .visible-dropdown {
        opacity:1;
    }

    .expandable {
        max-height:0px;
        overflow: hidden;
        transition:none;
    }

    .expandable:has(.sub-records) {
        max-height:2000px;
        transition: max-height .6s ease-in-out;
    }


    .dropdown-area .shown {

    }

    .bigger {
        height:400px;
    }

    .dropshadow {
    }

    .record-field {
        padding: 0.5rem;
    }

    .new-record {
        background-color: #f8f9fa;
    }
    .dropdown-area .new-record {
        background-color: rgba(76, 76, 76);
    }

    .dropdown-area .invisible-input  {
        color: white !important;;
    }
    th {
        padding-left:1rem;
        padding-right:1rem;
        padding-bottom:1rem;
    }

    .invisible-input {
        border-color: rgba(245, 245, 220, 0);
        background-color: transparent;
        outline: none;
    }

</style>

<script>
//import excel from 'vue-excel-export'

import adminPanelTableFieldSubscriptions from './admin-panel-table-field-subscriptions.vue'
import adminPanelTableField from './admin-panel-table-field.vue'

export default {
    props: {
        idField: {
            type: String,
            required: false,
            default: 'id'
        },
        recordType: {
            type: String,
            required: false,
            default: 'record'
        },
        tableData: {
            required: true,
            type: Array
        },
        fieldsMeta: {
            required: false,
            type: Object,
            default: function () {
                return {}
            }
        },
        actions: {
            type: Array,
            required: false,
            default: function () {
                []
            }
        },
        offerNewRecord: {
            type: Boolean,
            required: false,
            default: true
        },
        offerRemoveRecord: {
            type: Boolean,
            required: false,
            default: true
        }
    },
    components: {
        adminPanelTableFieldSubscriptions,
        adminPanelTableField
    },
    data() {
        return {
            sortField: {
                name: '',
                direction: 'asc',
                sortable: true,
                isActive: false
            },
            newRecord: {},
            editField: null,
            editRecord: null,
            editingValue: null,
            activeField: '',
            fields: [],
            dropDownRecord: {
                i: null,
                title: null
            },
            currentDisplay: '',
            searchQuery: '',
            searchResults: [],
            currentData: [],
            experimentStageMap: {
                0: 'Challenge',
                1: 'Idea',
                2: 'Project',
                3: 'Insight',
                4: 'Topic',
                5: 'Mission'
            },
            filterBy: []
        }
    },
    watch: {
        tableData: function() {
            this.cancelEditFieldMode()
            this.newRecord = {}
        }
    },
    computed: {
        excelFields: function() {
//            return Object.entries(this.fieldsMeta).map(field => field[1].label)
            // return an object where the key is the label and the value is the field name
            return Object.entries(this.fieldsMeta).reduce((acc, field) => {
                acc[field[1].label] = field[0]
                return acc
            }, {})

        },
        dropDownData: function() {
//            return this.dropDownRecord.data
        },
        requiredFields () {
            return Object.entries(this.fieldsMeta).filter(field => field[1].required)
        },
        canAddNewRecord () {
            return this.requiredFields.every(field => this.newRecord[field[0]])
        },
        displayedData() {
            var dataToReturn = this.search(this.tableData)
            dataToReturn = this.sortByField(dataToReturn)
            if (this.filterBy.length !== 0) {
                dataToReturn = this.filterByField(dataToReturn)
            }
            return dataToReturn
        }
    },
    methods: {
        getTotalForField (field) {
            return this.tableData.reduce((acc, record) => {
                return acc + parseFloat(record[field])
            }, 0)
        },
        log: function (data) {
            console.log(data)
        },
        removeRecord(record) {
            // Are you sure?
            if (confirm('Are you sure you want to delete this record?')) {
                this.$emit('remove-record', record)
            }

        },
        expandRecord(i, record, title = 'Record') {
            if (this.dropDownRecord.i == i) {
                this.dropDownRecord.i = null
                this.dropDownRecord.title = null
            } else {
                this.dropDownRecord.i = i
                this.dropDownRecord.title = title
            }
        },
        submitNewRecord () {
            this.$emit('new-record', this.newRecord)
        },
        saveEditField(field, record, value) {
            
            this.$emit('save-field', {
                field: field, 
                record: record, 
                value: value,
                recordValue: this.tableData[record][field]
            })

        },
        editFieldMode (field, record, value) {
            this.editField = field
            this.editRecord = record
            this.editingValue = value
        },
        alert (message) {
            alert(message)
        },
        cancelEditFieldMode () {
            this.editField = null
            this.editRecord = null
            this.editingValue = null
        },
        sortByField(sortData) {
            let order = this.sortField.direction
            var sortField = this.sortField
            this.activeField = this.sortField.name
            sortData.sort(function(a, b) {
                var textA = ''
                var textB = ''
                if (a[sortField['name']]) {
                    textA = a[sortField['name']]
                }
                if (b[sortField['name']]) {
                    textB = b[sortField['name']]
                }
                if (typeof textA === 'string') {
                    textA = textA.toLowerCase()
                    textB = textB.toLowerCase()
                }
                if (sortField['type'] == 'number') {
                    textA = parseFloat(textA)
                    textB = parseFloat(textB)
                }
                if (order === 'asc') {
                    return (textA > textB) ? -1 : (textA < textB) ? 1 : 0
                } else if (order === 'desc') {
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0
                }
            })
            return sortData
        },
        changeSortField(field) {
            return true // Disable sorting for now
            this.sortField.name = field.name
            this.sortField.type = field.type
            this.switchSortOrder()
        },
        switchSortOrder() {
            return true // Disable sorting for now            
            let switchMap = {'asc': 'desc', 'desc': 'asc'}
            this.sortField.direction = switchMap[this.sortField.direction]
        },
        search(data) {
            var searchResults = []

            data.forEach((item, i) => {
                let matches = Object.values(item).filter(e =>
                                                            e !== null &&
                                                            typeof e === 'string' &&
                                                            (e.includes(this.searchQuery) ||
                                                            e.toLowerCase().includes(this.searchQuery))
                                                        )
                if (matches.length !== 0) {
                    if (!searchResults.includes(data[i])) {
                        searchResults.push(data[i])
                    }
                }
            })
            return searchResults
        },
        filterByField(data) {
            var filteredResults = []
            data.forEach((item, i) => {
                this.filterBy.forEach((filterCriteria) => {
                    var fieldToFilter = Object.keys(filterCriteria)[0]
                    if (item[fieldToFilter] === filterCriteria[fieldToFilter]) {
                        filteredResults.push(item)
                    }
                })
            })
            return filteredResults
        }
    }

}
</script>

<style lang="css" scoped>
</style>
