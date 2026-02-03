<template>

    <div class="w-100">
        <div v-if="editable" class="d-flex">
            <a @click="edit = true" v-if="!edit" class="ml-auto edit-button-pencil"><i class="fa fa-pencil"></i> {{ $trans('Edit intro') }}</a>
            <a @click="edit = false" v-if="edit" class="ml-auto edit-button-pencil"><i class="fa fa-pencil"></i> {{ $trans('Finish editing') }}</a>
        </div>

        <div v-if="editable && noIntroCurrently && !edit" class="mt-4">
            {{ $trans('There is no intro set for this group yet. You can ') }} 
            <a @click="edit = true" v-if="!edit" class="ml-auto edit-button-pencil"><i class="fa fa-pencil"></i> {{ $trans('create an intro now.') }}</a>

        </div>
        <ui-panel class="bg-white">
            <ui-custom-language :edit="edit" @language-updated="saveTranslation" :custom-language="customLanguage" :field="'intro'" />
        </ui-panel>


    </div>


</template>

<script>

import backend from '../../backend.js'

export default {
    'name': 'group-intro',
    data() {
        return {
            edit: false
        }
    },
    methods: {
        saveTranslation(language, field, langCode) {
            backend.patch('/happenings/' + this.happeningId + '/custom_language/' + langCode + '/' , {
                custom_language: {
                    [field]: {
                        value: language,
                        format: 'html'
                    }
                }
            }).then((response) => {

                this.$emit('language-updated')

                this.$stores.ui.notifySuccess('Translation saved')

            }, (error) => {
                console.log(error)
                alert('error ...')
            })

        }
    },
    computed: {
        noIntroCurrently: function() {
            if (Object.keys(this.customLanguage).length === 0) {
                return true
            }
            return false
        },
        editable: function() {
            if (this.isUserAuthenticated && this.$currentUser.user_type == 'admin') {                
                return true
            }
            return false
        }
    },
    props: {
        happeningId: {
            required: true,
            type: String
        },
        customLanguage: {
            required: true,
            type: Object
        },
        happeningId: {
            required: true,
            type: String
        }
    }
}

</script>