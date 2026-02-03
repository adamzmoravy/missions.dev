<template>

        <td @dblclick="editMode = true" @keyup.esc="cancelEditFieldMode" class="record-field border" :class="{'updating': updatingField, 'editing': editMode}" v-tooltip="field.description">

            <template v-if="editMode && field.editable">

                <template v-if="field.editable">

                    <ui-input-kennitala v-if="field.type == 'kennitala'" :list="'suggestions-' +  recordKey"  :class="field.type" class="w-100" :type="field.type" @keydown-enter="saveEditField"  @focusout="cancelEditFieldMode()" @keyup-esc="cancelEditFieldMode()" v-model="editingValue" />

                    <template v-else-if="field.type == 'list'">

                        <ui-input-list v-tooltip="field.description" v-model="editingValue" @item-added="saveEditField" @item-removed="saveEditField" class="mb-2" />

                    </template>

                    <ui-input-text v-else :list="'suggestions-' +  recordKey"  :class="field.type" class="w-100" :type="field.type" @keydown-enter="saveEditField"  @focusout="cancelEditFieldMode" @keyup-esc="cancelEditFieldMode" v-model="editingValue" />

                    
                </template>

            </template>
            <template v-else-if="!editMode || !field.editable">

                <input v-if="value && value.length > 30" :class="field.type" class="w-100 invisible-input" readonly type="text" :value="value">                
                <span style="padding-left:4px;" v-else>
                    <span v-if="field.type == 'number'">
                        {{value.toLocaleString('is-IS')}}
                    </span>
                    <span v-else-if="field.type == 'list'">
                        {{value.join(', ')}}
                    </span>

                    <span v-else>
                        {{value}}
                    </span>

                </span>
            </template>


            <div v-if="editMode && field.editable" class="small explainer-text mt-1">ENTER to <ui-a @click="saveEditField" class="underline">save</ui-a>. ESC to <ui-a @click="cancelEditFieldMode" class="underline">cancel</ui-a>.</div>        
            <div v-if="editMode && !field.editable" class="small explainer-text mt-1">Readonly: <ui-a @click="cancelEditFieldMode" class="underline">Cancel</ui-a>.</div>        
        </td>


<!--

                    <td v-for="(field, key) in fieldsMeta" @dblclick="editFieldMode(key, i, record[key])" class="record-field border">




                        <div v-if="field.component == 'subscriptions'">

                            <admin-panel-table-field-subscriptions 
                                :i="i"  
                                :record="record" 
                                @fetch-records="$emit('fetch-records')">
                            </admin-panel-table-field-subscriptions>

                        </div>
                        <div v-else-if="record[key] || editField == key">
                            <template v-if="editField == key && editRecord == i && field.editable == true">
                                
                                <ui-input-kennitala v-if="field.type == 'kennitala'" :list="'suggestions-' +  key"  :class="field.type" class="w-100" :type="field.type" @keydown-enter="saveEditField(record[key])"  @focusout="cancelEditFieldMode()" @keyup-esc="cancelEditFieldMode()" v-model="editingValue" />

                                <template v-else-if="field.type == 'list'">

                                    <ui-input-list v-tooltip="field.description" v-model="editingValue" @item-added="saveEditField(record[key])" @item-removed="saveEditField(record[key])" class="mb-2" />

                                </template>

                                <ui-input-text v-else :list="'suggestions-' +  key"  :class="field.type" class="w-100" :type="field.type" @keydown-enter="saveEditField(record[key])"  @focusout="cancelEditFieldMode()" @keyup-esc="cancelEditFieldMode()" v-model="editingValue" />
   

                            </template>
                            <input :class="field.type" v-else-if="editField == key && editRecord == i && field.editable == false" readonly disabled @focusout="cancelEditFieldMode()" @keyup-esc="cancelEditFieldMode()"  type="text" :value="editingValue">
                            <template v-else>
                                <input v-if="record[key].length > 30" :class="field.type" class="w-100 invisible-input" readonly type="text" :value="record[key]">
                                <span style="padding-left:4px;" v-else>
                                    <span v-if="field.type == 'number'">
                                        {{record[key].toLocaleString('is-IS')}}
                                    </span>
                                    <span v-else-if="field.type == 'list'">
                                        {{record[key].join(', ')}}
                                    </span>

                                    <span v-else>
                                        {{record[key]}}
                                    </span>

                                </span>
                            </template>
                            <div class="x-small" v-if="editField == key && editRecord == i && field.editable == true">ENTER to save. ESC to cancel.</div>
                        </div>
                    </td>

-->

</template>

<style scoped>
.explainer-text {
    color: #6c757d;
}
.updating {
    pointer-events: none;
    opacity:0.5;
}

.editing {
    background-color: #e2e3e5;
}

</style>

<script>

import adminPanelTableFieldSubscriptions from './admin-panel-table-field-subscriptions.vue'

export default {
  name: "AdminPanelTableField",
  data() {
    return {
        editMode: false,
        editingValue: this.value,
        updatingField: false
    }
  },
  components: {
    adminPanelTableFieldSubscriptions
  },
  methods: {
    saveEditField() {

        if (this.editingValue == this.value) {
            this.editMode = false
            return
        }

        this.$emit('update-field', this.editingValue)
        this.updatingField = true
        
    },
    cancelEditFieldMode() {
        if (this.updatingField) {
            return
        }
        this.editingValue = this.value
        this.editMode = false

    }
  },
  watch: {
    value(newValue) {
        if (newValue == this.editingValue && this.updatingField) {
            this.editMode = false
            this.updatingField = false
        }

    }
  },
  props: {
    field: {
      type: Object,
      required: true
    },
    value: {
      type: [String, Number, Array, Object],
      required: true
    },
    recordKey: {
      type: String,
      required: true
    }
  },
}
</script>