import { defineStore, acceptHMRUpdate } from 'pinia'
import backend from '../backend.js'
import { useAuthStore } from '../stores/auth'

export const useGroupsStore = defineStore('groups', {
    state: () => {
      return { 
        groups: [],
        groupPreview: null,
        groupComments: [],
        calendarEvents: []
      }
    },
    actions: {
      createGroup (group) {
        backend.post('/happenings/', group).then((response) => {
          this.fetchGroupPreviews()
        })
      },
      updateGroup (config, groupId) {
          return backend.post('/happenings/' + groupId + '/config/',
            {
                config: config
            }
            ).then((response) => {
              this.fetchGroupPreviews()
            })
      },
      handleEvent(event) {
        if (event.eventName == 'comment_happening') {
          this.fetchGroupComments()
        }
        if (event.eventName == 'calendar_event_updated') {
          this.fetchCalendarEvents()
        }
        if (event.eventName == 'user-login' || event.eventName == 'user-logout') {
          this.fetchGroupComments()
        }
      },
      isCurrentUserRegistered (groupId) {
        if (this.getCurrentUser()) {
          let group = this.getGroupPreview(groupId)

          if (typeof group == 'undefined') {
            return false
          }

          return group.registrations.includes(this.getCurrentUser().user_id)
        }
        return false
      },
      previewIfRequired (groupId, next = false) {
        if (!this.isCurrentUserRegistered(groupId)) {
          if (this.getGroupPreview(groupId).config.access == 'private') {
            this.groupPreview = groupId
          } else {
            if (next) {
              next()
            }
          } 
        } else if (next) {
          next()
        }
      },
      async fetchCalendarEvents () {
        try {  
            const results = await backend.get('/calendar/events/')
            this.calendarEvents = results.data.calendar_events
          }
          catch (error) {
            console.log(error)
          }

      },
      async fetchGroupPreviews () {
        try {
            const results = await backend.get('/happenings/')
            this.groups = results.data.happenings
        }
        catch (error) {
          console.log(error)
        }
      },
      async fetchGroupComments () {
        try {
          const results = await backend.get('/happenings/topics/')
          this.groupComments = results.data.comments
        }
        catch (error) {
          console.log(error)
        }
      },
      async fetchSingleComment (commentId) {
        backend.get('/happenings/topics/', null, {comment_id: commentId}).then((response) => {
          if (response.data.comments.length > 0) {

            let comment = response.data.comments[0]
            let existingComment = this.groupComments.find(function (existingComment) {
              return existingComment.comment_id == comment.comment_id
            })

            if (existingComment) {
              this.groupComments.splice(this.groupComments.indexOf(existingComment), 1, comment)
            } else {
              this.groupComments.push(comment)
            }


          } else {

            let existingComment = this.groupComments.find(function (existingComment) {
              return existingComment.comment_id == commentId
            })

            if (existingComment) {
              this.groupComments.splice(this.groupComments.indexOf(existingComment), 1)
            }

          }
        })

      }
    },
    getters: {
      getCurrentUser: (state) => {
        return function () {
          let auth = useAuthStore()
          if (auth.isAuthenticated) {
            return auth.userData
          }
          return null
        }
      },
      getGroupPreview: (state) => {
        return function (groupId) {
          let group = this.groups.find(function (group) {
            return group.happening_id === groupId          
          })
          return group
        }
      },
      getGroupPreviewByUUID: (state) => {
        return function (groupUUID) {
          let group = this.groups.find(function (group) {
            return group.uuid === groupUUID          
          })
          return group
        }
      },
      getEntitiesByMember: (state) => {
        return function (ownerId) {
          let entities = this.getEntities()
          return entities.filter(function (group) {
            return group.data.registrations.includes(ownerId)
          })
        }
      },
      getEntities: (state) => {
        return function (applySorters = [], applyFilters = []) {
          let entities = []
          this.getSortedGroups(applySorters, applyFilters).forEach(function (group) {
            let entity = toEntity(group)
            entities.push(entity)
          })
          return entities
        }
      },
      getSortedGroups: (state) => {
        return function (applySorters = [], applyFilters = []) {
          let filteredGroups = this.getFilteredGroups(applyFilters)
  
            for (let i = 0; i < applySorters.length; i++) {
              let direction = 1
              if (applySorters[i].direction && applySorters[i].direction == 'asc') {
                direction = -1
              }
              filteredGroups.sort(function (a, b) {
                return sorters[applySorters[i].sorter](a, b, direction)
              })
  
            }
  
            return filteredGroups
  
        }
      },
      getFilteredGroups: (state) => {
        return function (applyFilters = []) {
  
          return state.groups.filter(function (group) {
  
            for (let i = 0; i < applyFilters.length; i++) {
              let selected = filters[applyFilters[i].selection](group, applyFilters[i].value)
              if (!selected) return false
            }
            return true
  
          })
          
        }
      }
    },
    persist: false
  })

  const filters = {
    'member': function (group, value) {
      return group.registrations.includes(value)
    },
    'search': function (group, value) {
      return group.name.toLowerCase().includes(value.toLowerCase())
    }
  }
  
  const sorters = {
    'name': function (a, b, direction) {
      if (a.name < b.name) return -1 * direction
      if (a.name > b.name) return 1 * direction
      return 0
    },
    'registrations_count': function (a, b, direction) {
      if (a.registrations.length < b.registrations.length) return -1 * direction
      if (a.registrations.length > b.registrations.length) return 1 * direction
      return 0
    },
    'created': function (a, b, direction) {
      if (a.happening_id < b.happening_id) return -1 * direction
      if (a.happening_id > b.happening_id) return 1 * direction
      return 0
    }
  }


let toEntity = function (group) {
    return  {
      title: group.name,
      link: '/group/' + group.happening_id + '/',
      icon: null,
      type: 'group',
      data: group,
      id: group.happening_id
    }
}

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useGroupsStore, import.meta.hot))
  }