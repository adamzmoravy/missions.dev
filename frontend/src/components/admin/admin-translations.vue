<template>
    
    <div v-if="platformTranslations">

        <div class="container-fluid pt-4 pl-4">
            <ui-button class="mr-2" size="md" @click="showMissingTranslations = !showMissingTranslations">{{ $trans('Missing translations') }}</ui-button>
            <div class="p-5" v-if="showMissingTranslations">
                <pre>{{ missingTranslationsJSON }}</pre>
            </div>
        </div>
        <div class="container-fluid">

            <div class="p-2">
                <div class="my-4">
                    <div class="d-flex">
                        <div v-for="lang in languages" :key="lang" class="mr-2 language-option">
                            <input type="radio" :id="lang" :name="lang" :value="lang" v-model="selectedLanguage">
                            <label class="ml-1" :for="lang">{{ $trans(lang.toUpperCase() + '_language')}}</label>
                        </div>                
                    </div>

                </div>
                <table class="table-hover">
                    <thead class="admin-table-header">
                        <th class="p-2">Key</th>
                        <th class="p-2">Default</th>
                        <th class="p-2">Platform</th>
                    </thead>
                    <tbody>
                        <tr v-for="(value, key) in selectedLanguageTranslations">
                            <td class="border p-2"><strong>{{ key }}</strong></td>
                            <td class="border p-2">
                                <span v-if="defaultTranslations[selectedLanguage][key]">{{ defaultTranslations[selectedLanguage][key] }}</span>
                            </td>
                            <td @dblclick="setEditingField(key, selectedLanguage)" class="border p-2">
                                <input autofocus @keyup.esc="clearField" @keyup.enter="saveField" class="w-100" v-if="editingField && editingField.lang == selectedLanguage && editingField.key == key" type="text" v-model="editingFieldValue">
                                <span v-else-if="platformTranslations[selectedLanguage] && platformTranslations[selectedLanguage][key]">{{ platformTranslations[selectedLanguage][key] }}</span>
                            </td>

                        </tr>
                    </tbody>
                </table>


            </div>
        </div>

    </div>


</template>

<style scoped>

    .language-option {
        border: 1px solid #ccc;
        padding: 5px;
        display:flex;
        align-items:center;
        justify-content: center;
        border-radius: 10px;
    }

    .language-option label {
        margin-bottom:2px;
    }

</style>


<script>
import { useTranslationsStore } from '../../stores/translations'
import { mapStores } from 'pinia'
import backend from '../../backend.js'
export default {
    props: {
    },
    data() {
        return {
            selectedLanguage: 'en',
            editingField: null,
            editingFieldValue: null,
            showMissingTranslations: false
        }
    },
    methods: {
        saveField: function () {
            backend.put('/platform/translations/' + this.editingField.lang + '/', {
                value: this.editingFieldValue,
                key: this.editingField.key
            }).then(response => {
                this.clearField()
                this.translationsStore.fetchPlatformTranslations()
            })
        },
        clearField: function () {
            this.editingField = null
            this.editingFieldValue = null
        },
        setEditingField: function (key, lang) {
            this.editingField = {
                key: key,
                lang: lang
            }
            this.editingFieldValue = this.platformTranslations[lang][key]
        }
    },    
    computed: {
        missingTranslationsJSON: function () {

            let missingTranslations = this.$stores.translations.missingTranslations
            return JSON.stringify(missingTranslations, null, 2)

        },
        ...mapStores(useTranslationsStore),
        translations: function () {
            return this.translationsStore.translations
        },
        languages: function () {
            return this.translationsStore.languages
        },
        defaultTranslations: function () {
            return this.translationsStore.defaultTranslations
        },
        selectedLanguageTranslations: function () {
            return this.translationsStore.translations[this.selectedLanguage]
        },
        platformTranslations: function () {
            return this.$stores.translations.platformTranslations
        }
    },
    mounted() {
        this.$stores.translations.fetchPlatformTranslations()
    }
}
</script>

<style lang="css" scoped>
</style>
