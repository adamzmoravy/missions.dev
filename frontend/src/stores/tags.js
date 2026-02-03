import { defineStore } from 'pinia'
import backend from '../backend.js'

export const useTagsStore = defineStore('tags', {
  state: () => ({
    tags: []
  }),
  actions: {
    async fetchTags(lang = 'en') {
      const response = await backend.get(`/tags/${lang}`)
      this.tags = response.data.tags
    },
    async createTag({ label, type, lang = 'en' }) {
      await backend.post(`/tags/${lang}`, { label, type })
      await this.fetchTags(lang)
    },
    async updateTag({ id, field, value, lang = 'en' }) {
      await backend.put(`/tags/${id}/${lang}`, { [field]: value })
      await this.fetchTags(lang)
    },
    async removeTag({ id, lang = 'en' }) {
      await backend.delete(`/tags/${id}/`)
      await this.fetchTags(lang)
    }
  },
  getters: {
    getTagById: (state) => {
      return (id) => state.tags.find(tag => tag.id === id)
    },
    getTagsByIds: (state) => {
      return (ids) => state.tags.filter(tag => ids.includes(tag.id))
    },
    getTagsByTagsWithType: (state) => {
        return (tagsWithType) => {
            let tags = []
            tagsWithType.forEach(tagWithType => {
                const matchedTags = state.tags.filter(tag => tagWithType.tag_id == tag.id)
                tags = tags.concat(matchedTags)
            })
            return tags
        }
    },
    getTypesAndTags: (state) => {
        return (tagsWithType) => {
          // within tagsWithType, there is a property 'types' which is an array of types for each tag, e.g. ['degree', 'role'], get all unique types and their associated tags
          const typesMap = {}
          tagsWithType.forEach(tagWithType => {
              const matchedTags = state.tags.filter(tag => tagWithType.tag_id == tag.id)
              matchedTags.forEach(tag => {
                  if (tagWithType.types) {
                      tagWithType.types.forEach(type => {
                          if (!typesMap[type]) {
                              typesMap[type] = []
                          }
                          typesMap[type].push(tag)
                      })
                  }
              })
          })
          console.log('typesMap', typesMap)
          return typesMap
        }
    },
    getTags: (state) => {
      return state.tags
    },
    getTagsByType: (state) => {
        return (type) => state.tags.filter(tag => tag.type === type)
    },
    getTagsByTypes: (state) => {
        return (types) => state.tags.filter(tag => tag.types && tag.types.some(t => types.includes(t)))
    }
  }
})