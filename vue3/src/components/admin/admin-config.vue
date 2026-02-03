<template>

    <div class="p-4">
        <ui-panel class="mb-3 warning">Warning: Direct editing of config is for advanced users only. You may mess something up.</ui-panel>

        <h1 class="large">Platform config</h1>
        <hr>

        <ui-panel v-for="item in configItems" class="mb-3">
            <admin-config-item  v-if="platform[item]" :config-key="item" :value="platform[item]" :schema="schemas[item]" />
        </ui-panel>

        <h1 class="large mt-4">UI config</h1>
        <hr>
        <ui-panel v-for="item in uiConfigItems"  class="mb-3">
            <admin-config-item  v-if="ui && ui[item] != undefined" type="ui" :config-key="item" :value="ui[item]" :schema="schemas[item]" />
        </ui-panel>


        <ui-panel v-if="false" class="config mb-3 py-2" v-for="(configItem, configKey) in platform" :key="configKey">
            <a @click="toggleItem('platform-' + configKey)"><h3 class="medium-2 mb-0">{{ configKey }}</h3></a>
            <div v-if="itemOpened.includes('platform-' + configKey)" class="mt-2">
                <v-jsoneditor :options="jsonEditorOptions" class="editor" v-model="platform[configKey]" :plus="true" @error="console.log('error')" />
                <div class="p-3">
                    <input type="radio" value="code" :name="'json-options-' + configKey" v-model="jsonEditorOptions.mode"> Code
                    <input type="radio" value="tree" :name="'json-options-' + configKey" v-model="jsonEditorOptions.mode"> Tree
                    <div class="float-right">CTR-i to format</div>
                </div>

            </div>
        </ui-panel>
    </div>

</template>

<style scoped>
.editor {
    min-height: 150px;
}
.warning {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

</style>

<script>
import backend from '../../backend.js'

import adminConfigItem from './admin-config-item.vue'

export default {
    name: 'admin-panel-config',
    data() {
        return {
            configItems: ['site_languages'],
            uiConfigItems: ['profileBackground', 'adminSections', 'mainMenuShow'],
            schemas: {},
            itemOpened: [],
            platform: {},
            ui: {},
            jsonEditorOptions: {mainMenuBar: false, mode: 'code'}
        }
    },
    components: {
        adminConfigItem
    },
    methods: {
        toggleItem(item) {
            if (this.itemOpened.includes(item)) {
                this.itemOpened.splice(this.itemOpened.indexOf(item), 1)
            } else {
                this.itemOpened.push(item)
            }
        },
        fetchConfig() {
            backend.get('/platform/config/').then((response) => {
                this.platform = response.data.config.platform
                this.ui = response.data.config.ui
            })
        }
    },
    mounted() {
        for (let item in this.configItems) {
            import(`../../../../config/config_item_schemas/${this.configItems[item]}.json`).then((schemas) => {
                this.schemas[this.configItems[item]] = schemas.default

            })
        }

        for (let item in this.uiConfigItems) {
            import(`../../../../config/config_item_schemas/ui.${this.uiConfigItems[item]}.json`).then((schemas) => {
                this.schemas[this.uiConfigItems[item]] = schemas.default
            })
        }


        this.fetchConfig()
    },

}

</script>