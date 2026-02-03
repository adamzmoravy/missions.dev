import { defineStore, acceptHMRUpdate } from 'pinia'
import { useProfilesStore } from './profiles'
import { useSubmissionsStore } from './submissions'
import { useGroupsStore } from './groups'
import { useSpacesStore } from './spaces'
import { ref, reactive, computed, watch, onMounted } from 'vue'


export const useEntitiesStore = defineStore('entities', () => {
    
    const profiles = useProfilesStore()
    const submissions = useSubmissionsStore()
    const groups = useGroupsStore()
    const spaces = useSpacesStore()


    const getFilteredEntities = function (applyFilters = []) {

        return entities.value.filter(function (entity) {

          for (let i = 0; i < applyFilters.length; i++) {
            let selected = filters[applyFilters[i].selection](entity, applyFilters[i].value)
            if (!selected) return false
          }
          return true

        })
        
    }

    const getSortedEntities = function (applySorters = [], applyFilters = []) {
        let filteredEntities = this.getFilteredEntities(applyFilters)

          for (let i = 0; i < applySorters.length; i++) {
            let direction = 1
            if (applySorters[i].direction && applySorters[i].direction == 'asc') {
              direction = -1
            }
            filteredEntities.sort(function (a, b) {
              return sorters[applySorters[i].sorter](a, b, direction)
            })

          }

          return filteredEntities
    }

    const entities = computed(() => {

        let entities = []
        entities = entities.concat(
            profiles.getEntities(),
            groups.getEntities(),
            submissions.getEntities(),
            spaces.getEntities()
        )
        return entities

    })

  return {entities, getFilteredEntities, getSortedEntities}

})

  const filters = {
      'search': function (entity, value) {
          return entity.title.toLowerCase().includes(value.toLowerCase())
      },
      'type': function (entity, value) {
          return entity.type == value
      },
      'id': function (entity, value) {
          return entity.id == value
      }
  }
  
  const sorters = {
    'title': function (a, b, direction) {
      if (a.title < b.title) return -1 * direction
      if (a.title > b.title) return 1 * direction
      return 0
    },
    'random': function (a, b, direction) {
      // sort randomly
      return Math.random() - 0.5
    }  
  }
  

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useEntitiesStore, import.meta.hot))
  }