import { defineStore} from 'pinia'
import { ref } from 'vue'
import en from '../../../translations/en.json'
import is from '../../../translations/is.json'
import backend from '../backend.js'

const translationsFromFiles = {
    'en': en,
    'is': is
}

export const useTranslationsStore = defineStore('translations', {
  state: () => {
    return { 
        defaultTranslations: translationsFromFiles,
        platformTranslations: {},
        selectedLanguage: 'en',
        missingTranslations: {
            'en': {},
            'is': {}
        },
    }
  },
  getters: {
    trans: (state) => {
        return (key) => {
            if (state.translations[state.selectedLanguage][key]) {
                return state.translations[state.selectedLanguage][key]
            }
            if (state.translations['en'][key]) {
                
//                console.log(state.selectedLanguage + ' translation not found for key: ' + key)
                state.missingTranslations[state.selectedLanguage][key] = state.translations['en'][key]
                return state.translations['en'][key]
            }

            state.missingTranslations['en'][key] = key

//            console.log('Translation not found for key: ' + key)
            return key
        }
    },
    languages: (state) => Object.keys(state.translations),
    translations() {
        let combinedTranslations = this.defaultTranslations
        for (let lang in this.platformTranslations) {
            if (!(lang in combinedTranslations)) {
                combinedTranslations[lang] = {}
            }
            for (let key in this.platformTranslations[lang]) {
                combinedTranslations[lang][key] = this.platformTranslations[lang][key]
            }
        }
        return combinedTranslations
    }
  },
  actions: {
    setLanguage(lang) {
        if (lang != undefined) {
            this.selectedLanguage = lang
        }

    },
    fetchPlatformTranslations() {
        backend.get('/platform/')
        .then(response => {
            this.setPlatformTranslations(response.data.translations)
        })
        .catch(error => {
            console.log(error)
        })
    },
    setPlatformTranslations(translations) {
      this.platformTranslations = translations
    },
  },
  persist: false
})

