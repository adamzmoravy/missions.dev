import { defineStore, acceptHMRUpdate } from 'pinia'
import { useLocalStorage } from "@vueuse/core"
import { useEventsStore } from './events'

import backend from '../backend.js'

export const useAuthStore = defineStore('auth', {
    state: () => {
      return { 
//        token: useLocalStorage('token', {string: null, timestamp: null, set: false}),
//        userData: useLocalStorage('userData', {})
        token: {string: null, timestamp: null, set: false},
        userData: {}
        }
    },
    actions: {
        handleEvent(event) {
            if (event.eventName == 'user_updated') {
                if (event.data.event.user_id == this.userData.user_id) {
                    this.setUserData(this.token.string)
                }
            }
        },
        logout () {
            this.removeToken()
            const eventsStore = useEventsStore()
            eventsStore.addEvent('user-logout', {})
        },
        login() {
            let tokenString = localStorage.getItem('token')
            let token = tokenString ? JSON.parse(tokenString) : null
            if (token && token.set) {
                this.token = token
                this.setUserData(this.token.string)
            } else {
                const eventsStore = useEventsStore()
                eventsStore.addEvent('user-not-login')                
            }
        },
        async register(credentials) {
            return backend.post('/users/', credentials).then((response) => {
                return response
            })        
        },
        setToken (token) {
            let tokenObject = {
                'string': token,
                'timestamp': Date.now(),
                'set': true
            }
            this.token = tokenObject
            // set the user data
            this.setUserData(token)
            localStorage.setItem('token', JSON.stringify(tokenObject));

        },
        setUserData (token) {
            backend.get('/users/me/profile', token).then((response) => {
                let userData = response.data.profile
                this.userData = userData
                const eventsStore = useEventsStore()
                eventsStore.addEvent('user-login', userData)
            })
        },
        refreshUserData () {
            this.setUserData(this.token.string)
        },
        removeToken () {
            this.token = {string: null, timestamp: null, set: false}
            this.userData = {}
            localStorage.removeItem('token');

        }
    },
    getters: {
        getModal: (state) => {
            return state.modal
        },
        isAuthenticated: (state) => {
            return state.token.set
        }
    },
    persist: true
})


if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useAuthStore, import.meta.hot))
    }    