import { defineStore } from 'pinia'

import backend from '../backend.js'

import { useAuthStore } from './auth.js'

export const usePlatformStore = defineStore('platform', {

    state: () => (
        { 
            config: null, 
            ui: null,
            apiUrl: null,
            translations: null,
            platformTranslations: null,
            pusherKey: null,
            platformKey: null,
            loadingError: false
        }),
    getters: {
      platformLoaded: (state) => {
            return state.config != null && state.ui != null
      }
    },
    actions: {
        fetchConfig() {
            // get the platform config
            backend.get('/platform/').then((response) => {
                this.config = response.data.platform
                this.ui = response.data.ui
            }).catch((error) => {
                this.loadingError = true
                console.log('Error loading platform config fetch')
            })
        },
        initialize(apiUrl) {
            this.apiUrl = apiUrl
            // get the platform config
            backend.get('/platform/').then((response) => {
                this.config = response.data.platform
                this.ui = response.data.ui
            }).catch((error) => {
                this.loadingError = true

                if (error.response?.data?.status !== undefined) {
                    console.log('Error status:', error.response.data.status)

                    if (error.response.data.status === 'invalid_token')
                    {

                        const authStore = useAuthStore()

                        authStore.logout()


                    }
                }

                console.log('Error loading platform config initialize')
            })


        },
    },
  
})