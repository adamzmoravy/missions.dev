<template>

    <div>

        <ui-tabs v-if="languageTabs.length > 1" class="mt-0" v-on:update:section="showLanguage = $event" :tabs="languageTabs" :current-selection="showLanguage" />            

        <template v-for="language in languageTabs">
            <div v-if="language.value == showLanguage">

                <div>
                    <template v-if="customLanguage[language.value.toUpperCase()] && customLanguage[language.value.toUpperCase()][field]">
    
                        <ui-html :editable="edit" v-model="customLanguage[language.value.toUpperCase()][field].value" />

                        <ui-button v-if="edit" class="mt-2" size="md" button-type="new" @click="$emit('language-updated', customLanguage[language.value.toUpperCase()][field].value, field, language.value)">
                            {{$trans('Save')}}
                        </ui-button>

                    </template>

                    <template v-else-if="newLanguageFields[language.value.toUpperCase()]">

                        <ui-html :editable="edit" v-model="newLanguageFields[language.value.toUpperCase()][field].value" />

                        <ui-button v-if="edit"  class="mt-3" size="md" button-type="new" @click="$emit('language-updated', newLanguageFields[language.value.toUpperCase()][field].value, field, language.value)">
                            {{$trans('Add')}}
                        </ui-button>


                    </template>


                </div>

            </div>
        </template>

    </div>

</template>


<script>


export default {
    'name': 'ui-custom-language',
    data() {
        return {
            showLanguage: 'en',
            editedCustomLanguage: {},
            newLanguageFields: {}
        }
    },
    emits: ['language-updated'],
    computed: {
        languageTabs () {

            if (this.edit) {
                let siteLanguages = this.$config.platform.site_languages
                return siteLanguages.map((language) => {
                    return {
                        display: this.$trans(language.toUpperCase() + '_language'),
                        value: language
                    }
                })

            } 

            // count properties of object this.customeLanguage
            if (Object.keys(this.customLanguage).length > 0) {
                return Object.keys(this.customLanguage).map((language) => {
                    return {
                        display: this.$trans(language + '_language'),
                        value: language.toLowerCase()
                    }
                })
            }

            return []

        }
    },
    emits: ['language-updated'],
    mounted() {

        if (this.customLanguage[this.$lang.toUpperCase()] && this.customLanguage[this.$lang.toUpperCase()][this.field]) {
            this.showLanguage = this.$lang
        } else if (this.languageTabs[0]) {
            this.showLanguage = this.languageTabs[0].value
        } else {
            this.showLanguage = this.$lang
        }

        let siteLanguages = this.$config.platform.site_languages
        siteLanguages.forEach((language) => {

            this.newLanguageFields[language.toUpperCase()] = {
                [this.field]: {
                    value: '',
                    format: 'html',
                }
            }
        
        })

    },
    props: {
        customLanguage: {
            required: true,
            type: Object
        },
        field: {
            required: true,
            type: String
        },
        edit: {
            required: false,
            type: Boolean,
            default: false
        }
    }
}

</script>