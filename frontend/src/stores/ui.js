import { defineStore, acceptHMRUpdate } from 'pinia'

import { useTitle } from '@vueuse/core'

import miniToastr from 'mini-toastr'
miniToastr.init({types: {success: 'success',error: 'error',info: 'info',warn: 'warn'}})

function toast({title,message,type,timeout,cb}) {
    return miniToastr[type](message, title, timeout, cb)
}

export const useUIStore = defineStore('ui', {
    state: () => {
      return { 
        modal: null,
        lang: 'en',
        pageMode: 'multipage',
        action: null,
        newSubmission: null,
        newPost: null,
        newEvent: null,
        platformName: '',
        loadMoreOnScroll: 0
      }
    },
    actions: {
        setPageTitle(title = null) {
            if (title) {
                useTitle(title + ' | ' + this.platformName)
            }
            else {
                useTitle(this.platformName)
            }
        },
        setModal(modal) {
            this.modal = modal
        },
        closeModal() {
            this.modal = null
        },
        setNewEvent(data = {}) {
            this.newEvent = data
        },
        setNewSubmission(data = {}) {
            this.newSubmission = data
        },
        setNewPost(data = {}) {
            this.newPost = data
        },
        setAction(action, data = null) {
            this.action = {
                name: action,
                data: data
            }
        },
        login () {
            this.modal = 'login-modal'
        },
        notifySuccess(message) {
            toast({
                title: 'Success',
                message: message,
                type: 'success',
                timeout: 3000
            })
        }
    },
    getters: {
        showPrivateScreens: (state) => {
            return state.pageMode == 'multipage'
        },
        getModal: (state) => {
            return state.modal
        }
    }
})


if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useUIStore, import.meta.hot))
    }    