import { defineStore, acceptHMRUpdate } from 'pinia'
import backend from '../backend.js'


let placeholders = {
  profile: {
    user_id: null,
    first_name: '',
    last_name: '',
    short_description: '',
    long_description: '',
    phone: '',
    document_number: '',
    tags: [],
    links: [],
    experiments: [],
    type: 'guest'
  },
}

export const useProfilesStore = defineStore('profiles', {
  state: () => {
    return { 
      profiles: [],
      profilePreviews: [],
      currentUser: placeholders.profile
    }
  },
  actions: {
    handleEvent(event) {
      if (event.eventName == 'user_updated') {
        this.fetchProfilePreviews()
      }
    },
    async fetchProfilePreviews () {
      try {
        const results = await backend.get('/users/preview/')
        this.profilePreviews = results.data.previews
      }
      catch (error) {
        console.log(error)
      }
    },
    async fetchProfile(userId, silentFail = true) {
      try {
          const results = await backend.get('/users/' + userId + '/profile', null, {}, silentFail)
          let index = this.profiles.findIndex(profile => String(profile.user_id) === String(userId))  

          if (userId == 'me') {
            this.currentUser = results.data.profile
            return true
          }

          if (index === -1) {
            this.profiles.push(results.data.profile)
          } else {
            this.profiles[index] = results.data.profile
          }
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
        this.getSortedProfiles(applySorters, applyFilters).forEach(function (profile) {
          let entity = {
            title: profile.first_name + ' ' + profile.last_name,
            link: '/profile/' + profile.user_id + '/',
            icon: profile.image,
            type: 'profile',
            data: profile,
            id: profile.user_id
          }
          entities.push(entity)
        })
        return entities
      }
    },
    getSortedProfiles: (state) => {
      return function (applySorters = [], applyFilters = []) {
        let filteredProfiles = this.getFilteredProfiles(applyFilters)
        for (let i = 0; i < applySorters.length; i++) {
          let direction = 1
          if (applySorters[i].direction && applySorters[i].direction == 'asc') {
            direction = -1
          }
          filteredProfiles.sort(function (a, b) {
            return sorters[applySorters[i].sorter](a, b, direction)
          })

        }

        return filteredProfiles

      }
    },
    getFilteredProfiles: (state) => {
      return function (applyFilters = []) {

        return state.profilePreviews.filter(function (profile) {

          for (let i = 0; i < applyFilters.length; i++) {
            let selected = filters[applyFilters[i].selection](profile, applyFilters[i].value)
            if (!selected) return false
          }
          return true

        })

      }
    },
    getProfilesCountries: (state) => {
      let countries = []
      state.profilePreviews.forEach((profile) => {
        if (profile.geographic_location?.country?.name) {
          const countryName = profile.geographic_location.country.name
          const existingCountry = countries.find(country => country.code === profile.geographic_location.country.code)
          if (existingCountry) {
            existingCountry.frequency++
          } else {
            countries.push({ name: countryName, frequency: 1, code: profile.geographic_location.country.code })
          }
        }
      })
      countries.sort((a, b) => b.frequency - a.frequency)
      return countries
    },
    getProfilePreviewsByIds: (state) => {
      return function (userIds, showUndefined = true) {
        let profiles = []
        for (let i = 0; i < userIds.length; i++) {
          let profile = state.profilePreviews.find((profile) => String(profile.user_id) === String(userIds[i]))
          if (profile == undefined) {
            if (showUndefined) {
              profiles.push(placeholders.profile)
            }
          } else {
            profiles.push(profile)
          }
        }
        return profiles
      }
    },
    getProfilePreviewById: (state) => {
      return function (userId, silentFail = false) {
        let profile = null
        profile = state.profilePreviews.find((profile) => String(profile.user_id) === String(userId))
        if (profile == undefined) {
          console.log('silentFail: ' + silentFail)
          this.fetchProfile(userId, silentFail)
          return placeholders.profile
        } else {
          return profile
        }
      }
    },
    getProfileById: (state) => {
        return function (userId) {
          let profile = null
          if (userId == 'me') {
            profile = state.currentUser
          } else {
            profile = state.profiles.find((profile) => String(profile.user_id) === String(userId))
          }
          if (profile == undefined) {
            this.fetchProfile(userId)
            return placeholders.profile
          } else {
            return profile
          }
        }

    },
  },
  persist: false
})

const sorters = {
  'profile_pic': function (a, b, direction) {
    if (a.image) {
      return -1 * direction
    }
    return 0
  },
  'random': function (a, b, direction) {
    // sort randomly
    return Math.random() - 0.5
  },
  'date_joined': function (a, b, direction) {
    if (parseInt(a.user_id) < parseInt(b.user_id)) {
      return -1 * direction
    }
    if (parseInt(a.user_id) > parseInt(b.user_id)) {
      return 1 * direction
    }
    return 0    
/*    if (a.date_joined < b.date_joined) {
      return -1 * direction
    }
    if (a.date_joined > b.date_joined) {
      return 1 * direction
    }
    return 0
    */
  },
}

const filters = {
  'search': function (profile, value) {
    let fullName = profile.first_name + ' ' + profile.last_name
    return fullName.toLowerCase().includes(value.toLowerCase()) 
    || profile.short_description.toLowerCase().includes(value.toLowerCase())
  },
  'name': function (profile, value) {
    return profile.first_name.toLowerCase().includes(value.toLowerCase()) || profile.last_name.toLowerCase().includes(value.toLowerCase())
  },
  'ids': function (profile, value) {
    return value.includes(profile.user_id)
  }
}

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useProfilesStore, import.meta.hot))
}