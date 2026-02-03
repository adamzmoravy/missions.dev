import { defineStore, acceptHMRUpdate } from 'pinia'
import axios from 'axios'
import backend from '../backend.js'


export const useSpacesStore = defineStore('spaces', {
    state: () => {
      return { 
        spaces: []
      }
    },
    actions: {
      createNewSpace (space) {
        backend.post('/experiments/spaces/', space).then(response => {
          this.fetchSpacesPreviews()
        })
      },
      removeSpace (space) {
        backend.delete('/experiments/spaces/' + space.id + '/').then(response => {
          this.fetchSpacesPreviews()
        })
      },
      async fetchSpacesPreviews () {
        try {
            const results = await backend.get('/experiments/spaces/')
            this.spaces = results.data.spaces
        }
        catch (error) {
          console.log(error)
        }
      }
    },
    getters: {
      getEntities: (state) => {
        return function (applySorters = [], applyFilters = []) {
          let entities = []
          this.spaces.forEach(function (space) {
            let entity = {
              title: space.title,
              link: '/mesh/' + space.key + '/',
              icon: null,
              type: 'space',
              data: space,
              id: space.key
            }
            entities.push(entity)
          })
          return entities
        }
      }
    },
    persist: false
  })


if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useSpacesStore, import.meta.hot))
  }