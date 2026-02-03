<template>

    <div>
        <ui-json-editor v-if="value !== null" @value-update="setValue" :value="value" :schema="schema" />
        <ui-button @click="saveConfig(configKey, editedValue)">Save</ui-button>
    </div>

</template>

<style scoped>
</style>

<script>
import backend from '../../backend.js'

export default {
    name: 'admin-panel-config-item',
    data: function () {
        return {
            editedValue: null,
        }
    },
    props: {
        type: {
            required: false,
            default: 'default'
        },
        configKey: {
            required: true,
            default: null
        },
        value: {
            required: false,
            default: null
        },
        schema: {
            required: false,
            default: null
        }
    },
    methods: {
        setValue: function (value) {
            this.editedValue = value
        },
        saveConfig(key, value) {
            if (this.type == 'ui') {
                key = 'ui.' + key
            }
            backend.put('/platform/config/', {
                key: key,
                value: value
            }).then((response) => {
                alert('Config saved')
            })
        }        
    }
}


</script>