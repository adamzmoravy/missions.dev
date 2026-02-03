
import { defineStore, acceptHMRUpdate, mapState } from 'pinia'
import backend from '../backend.js'
import { useUIStore } from './ui'


export const useSubmissionsStore = defineStore('submissions', {
  state: () => ({
    submissions: []
  }),
  computed: {
  },
  getters: {
    getEntities: (state) => {
      return function (applySorters = [], applyFilters = []) {
        let entities = []
        this.getSortedSubmissions(applySorters, applyFilters).forEach(function (submission) {
          let entity = {
            title: submission.title,
            link: '/submission/' + submission.experiment_id + '/',
            icon: submission.image,
            type: 'submission',
            data: submission,
            id: submission.experiment_id
          }

          entities.push(entity)
        })
        return entities
      }
    },
    getSortedSubmissions: (state) => {
      return function (applySorters = [], applyFilters = []) {
        let filteredSubmissions = this.getFilteredSubmissions(applyFilters)

          for (let i = 0; i < applySorters.length; i++) {
            let direction = 1
            if (applySorters[i].direction && applySorters[i].direction == 'asc') {
              direction = -1
            }
            filteredSubmissions.sort(function (a, b) {
              return sorters[applySorters[i].sorter](a, b, direction)
            })

          }

          return filteredSubmissions

      }
    },
    getFilteredSubmissions: (state) => {
      return function (applyFilters = []) {

        return state.submissions.filter(function (submission) {

          for (let i = 0; i < applyFilters.length; i++) {
            let selected = filters[applyFilters[i].selection](submission, applyFilters[i].value)
            if (!selected) return false
          }
          return true

        })
        
      }
    },
    getLocations: (state) => {
      return function (stages = null) {
        let locations = []
        state.submissions.forEach(function (submission) {
          if (stages && !stages.includes(submission.stage)) {
            return
          }
          if (submission.geographic_location) {

              if (!locations.some(location => location.formatted_address === submission.geographic_location.formatted_address)) {
                locations.push(submission.geographic_location)
              }

          }
        }
        )
        return locations
      }
    },
    getSubmissionPreview: (state) => {
      return function (id) {

        if (typeof id !== 'string') {
          id = id.toString()
        }

        return state.submissions.find(submission => submission.experiment_id == id)
      }
    },
    getSubmissionPreviewByUUID: (state) => {
      return function (uuid) {
        return state.submissions.find(submission => submission.uuid == uuid)
      }
    }
  },
  actions: {
    handleEvent(event) {
      if (event.eventName == 'experiment_updated') {
        this.fetchSubmissions()
      }
    },
    async fetchSubmissions(lang = 'en') {

      const response = await backend.get('/experiments/preview/' + lang)
        this.submissions = response.data.previews

    }
  }
})

const filters = {
  'owner': function (submission, value) {
    return submission.team_ids.includes(value.toString())
  },
  'stages': function (submission, value) {
      return value.includes(submission.stage)
  },
  'ids': function (submission, value) {
    return value.map(v => v.toString()).includes(submission.experiment_id.toString())
  },
  'search': function (submission, value) {
    return submission.title.toLowerCase().includes(value.toLowerCase())
  },
  'address': function (submission, value) {

    if (!submission.geographic_location) {
      return false
    }

    return submission.geographic_location.formatted_address.toLowerCase() == value.toLowerCase()
  }
}

const sorters = {
  'title': function (a, b, direction) {
    if (a.title < b.title) return -1 * direction
    if (a.title > b.title) return 1 * direction
    return 0
  },
  'relevance': function (a, b, direction) {
    if (a.relevance > b.relevance) return -1 * direction
    if (a.relevance < b.relevance) return 1 * direction
    return 0
  },
  'created': function (a, b, direction) {
    if (a.created_at < b.created_at) return -1 * direction
    if (a.created_at > b.created_at) return 1 * direction
    return 0
  },
  'updated': function (a, b, direction) {
    if (a.last_major_update < b.last_major_update) return -1 * direction
    if (a.last_major_update > b.last_major_update) return 1 * direction
    return 0
  },
  'owner': function (a, b, direction) {
    if (a.owner < b.owner) return -1 * direction
    if (a.owner > b.owner) return 1 * direction
    return 0
  },
  'stage': function (a, b, direction) {
    if (a.stage < b.stage) return -1 * direction
    if (a.stage > b.stage) return 1 * direction
    return 0
  }
}

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useSubmissionsStore, import.meta.hot))
  }