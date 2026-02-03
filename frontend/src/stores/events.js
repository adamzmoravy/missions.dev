import { defineStore, acceptHMRUpdate } from 'pinia'
import Pusher from 'pusher-js'

export const useEventsStore = defineStore('events', {
    state: () => {
      return { 
        events: [],
        pusher: null
      }
    },
    actions: {
        initiatePusher(pusherKey) {

            var myPusher = new Pusher(pusherKey, {
                cluster: 'eu'
            })
            this.pusher = myPusher

        },
        subscribeToChannel(channelName) {

            let self = this
            let callback = function(eventName, data) {
                
                if (eventName.substring(0, 7) !== 'pusher:')  {
                  self.addEvent(eventName, data)
                }
            }
            let channel = this.pusher.subscribe(channelName)
            channel.bind_global(callback)
        },
        addEvent(eventName, data) {
            this.events.push ({
                eventName: eventName,
                data: data,
                time: new Date()
            })
            // if there are more than 100 events, remove the oldest one
            if (this.events.length > 100) {
                this.events.shift()
            }
        }
    },
    getters: {
        getEvents: (state) => {
            return state.events
        },
        getEventsByName: (state) => {
            return function (eventName) {
                return state.events.filter(event => event.eventName === eventName)
            }
        },
        getLatestEventByName: (state) => {
            return function (eventName) {
                let events = state.events.filter(event => event.eventName === eventName)
                if (events.length > 0) {
                    return events[events.length - 1]
                }
                return null
            }
        },
        getLatestEvent: (state) => {
            return function () {
                if (this.events.length > 0) {
                    return this.events[this.events.length - 1]
                }
                return null
            }
        }
    }
})


if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useEventsStore, import.meta.hot))
    }    