<template>

    <div>
        <h3 class="medium">{{ $trans('Title') }}</h3>
        <div v-for="language in langCodes" class="mb-3">
            <div>
                <strong v-if="langCodes.length > 1">{{ $trans(language + '_language') }}</strong>
                <input type="text" v-model="editedTranslations[language].title.value" class="form-control">
            </div>
        </div>
        <h3 class="medium">{{ $trans('Short description') }}</h3>
        <div v-for="language in langCodes" class="mb-3">
            <div>
                <strong v-if="langCodes.length > 1">{{ $trans(language + '_language') }}</strong>
                <textarea type="text" rows="4" v-model="editedTranslations[language].short_description.value" class="form-control"></textarea>
            </div>
        </div>

        <ui-button :working="saving" :disabled="!hasBeenEdited" button-type="new" :label="$trans('Save')" @click="save"></ui-button>
    </div>


</template>

<style scoped>


</style>

<script>
import backend from '../../backend.js'
export default {
    name: 'submission-intro-translations',
    data() {
        return {
            saving:false,
            hasBeenEdited: true,
            editedTranslations: {}
        }
    },
    watch: {
        editedTranslations: {
            handler: function (val, oldVal) {
                console.log('chenges!"!!"')
                // check if editedTranslations is different from this.translationsData
                if (JSON.stringify(val) != JSON.stringify(this.translationsData)) {
                    this.hasBeenEdited = true
                } else {
                    this.hasBeenEdited = false
                }
            },
            deep: false
        }
    },
    computed: {
        langCodes: function() {
            let codes = []
            for (let language of this.languages) {
                codes.push(language.toUpperCase())
            }
            return codes
        },
        title: function() {
            // return object with value for each language
            let title = {}
            for (let language of this.languages) {
                title[language] = this.getTranslation('title', language)
            }
            return title
        },
        sortDescription: function() {
            // return object with value for each language
            let desc = {}
            for (let language of this.languages) {
                desc[language] = this.getTranslation('short_description', language)
            }
            return desc
        }
    },
    props: {
        experimentId: {
            type: String,
            required: true
        },
        languages: {
            type: Array,
            required: true
        },
        translationsData: {
            type: Object,
            required: true
        }
    },
    mixins: [
    ],
    notifications: {
    },
    methods: {
        setEditedTranslations (translations) {

            // go through all this.languages and set this.editedTranslations[language] to translations[language]
            for (let language of this.langCodes) {

                if (translations[language] == undefined) {
                    translations[language] = {}
                }

                if (translations[language].title == undefined) {
                    translations[language].title = {
                        value: ''
                    }
                }

                if (translations[language].short_description == undefined) {
                    translations[language].short_description = {
                        value: ''
                    }

                }

                // add translations to this.editedTranslations
                this.editedTranslations[language] = translations[language]  

            
            }

        },
        save () {
            let data = {
                languages: this.editedTranslations
            }
            this.saving = true
            backend.patch('/experiments/' + this.experimentId + '/language/', data).then((response) => {
                this.saving = false
                this.$emit('saved')
            })
        },
        getTranslation (field, language) {

            // make languge uppercase
            language = language.toUpperCase()

            // if this.translationsData[language][field] is undefined 
            if (this.translationsData[language] == undefined) {
                // return empty string
                return ''
            }
            
            return this.translationsData[language][field]


        }
    },
    beforeDestroy () {
    },
    created: function() {
        this.setEditedTranslations(this.translationsData)

    }
}
</script>