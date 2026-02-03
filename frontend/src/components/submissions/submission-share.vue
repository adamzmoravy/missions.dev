<template>
    
    <ui-steps-modal :working="sendingSubmission" :title="'Share submission'"  @close="$emit('close')"  :stepsStyle="'yellow'" @finished="shareSubmissions" class="steps" :stages="stages" @stage-changed="stage = $event">


            <template #body="slotProps">

                <div class="w-100 h-100" v-if="slotProps.stageName == 'discover-people'" >

                    <div class="pt-3">
                        <h3 class="medium mb-4">Identify potential collaborators to share submission with.</h3>
                        <profile-finder class="h-100" 
                        :profiles="$stores.profiles.profilePreviews" 
                        :include-tag-types="tagTypes"
                        :order-of-tag-types="orderOfTagTypes"
                        :country-label="$trans('Collaborator location')"
                        @update="updateTags" @update-country="updateCountry" :layout="'minimalist'"></profile-finder>
                    </div>

                </div>

                <div class="w-100 h-100 pt-3" v-if="slotProps.stageName == 'review-people'" >
                
                    <h3 class="medium">Review the people you've selected to share this with, and add or remove as needed.</h3>
                    <entities 

                        :multi-select="true" 
                        :show-search-bar="true" 
                        @selected="selectedProfileIds.push($event.user_id)" 
                        @unselected="selectedProfileIds = selectedProfileIds.filter(id => id !== $event.user_id)" 
                        :multi-select-selected="selectedProfileIds" 
                        :include-link="false" 
                        :entities="allProfileEntities" 
                        :limit="5000" />

                </div>

                <div class="w-100 h-100 pt-3" v-if="slotProps.stageName == 'send'" :class="{ 'working': sendingSubmission }">
                
                    <h3 class="medium">Write a short note to explain why you are sharing this {{ $trans($ui.stageHandles[submission.stage]) }} with them.</h3>

                    <ui-textarea v-model="submission.share_note" :rows="6" :placeholder="'Let me know what you think about this ' + $trans($ui.stageHandles[submission.stage]) + '...'" class="w-100"></ui-textarea>


                <div class="row mt-3 justify-content-center">
                    <submission-preview class="col-md-6" :experiment="$stores.submissions.getSubmissionPreview(submission.experiment_id)"  />
                </div>

                </div>

            </template>

            <template #bottom="slotProps">

                    <div class="px-3" v-if="slotProps.stageName == 'discover-people' && profilesWithMatchingTags.length > 0">

                            <h3 class="medium mb-3">Found ({{ profilesWithMatchingTags.length }})</h3>
                            <profile-index-row :noclicks="true" :profiles="profilesWithMatchingTags" :maxShowNumProfiles="16" />
                    </div>

                    <div class="px-3" v-if="slotProps.stageName == 'review-people'">

                            <h3 class="medium mb-3">Selected ({{ selectedProfiles.length }})</h3>
                            <profile-index-row :noclicks="true" :profiles="selectedProfiles" :maxShowNumProfiles="16" />
                    </div>
                    <div class="px-3" v-if="slotProps.stageName == 'send'" :class="{ 'working': sendingSubmission }">

                            <h3 class="medium mb-3">Recipients ({{ selectedProfiles.length }})</h3>
                            <profile-index-row :noclicks="true" :profiles="selectedProfiles" :maxShowNumProfiles="16" />
                    </div>


            </template>




    </ui-steps-modal>


</template>

<style scoped>

.working {
    opacity: 0.6;
    pointer-events: none;
    user-select: none;
}

</style>


<script>

import profileFinder from '../profiles/profile-finder.vue'
import backend from '../../backend.js'
export default {
    name: 'SubmissionShare',
    data() {
        return {
            sendingSubmission: false,
            tagsToFind: [],
            countryFilter: '',
            tagsFilteringMode: 'matching-all',
            mode: 'steps',
            stage: 1,
            stages: [
                {
                    name: 'discover-people',
                },
                {
                    name: 'review-people',
                },
                {
                    name: 'send',
                }
            ],
            selectedProfileIds: []
        }
    },
    methods: {
         updateTags(tags) {
            this.tagsToFind = tags
        },
        updateCountry(country) {
            this.countryFilter = country
        },
        shareSubmissions() {
            this.sendingSubmission = true

            backend.post('/experiments/' + this.submission.experiment_id + '/share/', {
                profile_ids: this.selectedProfileIds,
                message: this.submission.share_note
            }).then(response => {
                this.$stores.ui.notifySuccess('Submission shared')
                this.sendingSubmission = false
                this.$emit('shared', response.data)
                this.$emit('close')

            }).catch(error => {
                this.sendingSubmission = false
                console.error('Error sharing submission:', error)

            })

        }
    },
    watch: {
        profilesWithMatchingTags(newVal) {
            this.selectedProfileIds = newVal.map(profile => profile.user_id)
        }
    },
    computed: {
        tagTypes() {
            return [
                {
                    "type": "roles",
                    "label": "Collaborator Role(s)"
                },
                {
                    "type": "populations",
                    "label": "Populations Collaborator primarily works with"
                },
                {
                    "type": "environments",
                    "label": "Environments Collaborator primarily works in"
                },
                {
                    "type": "interests",
                    "label": "Collaborator areas of interest"
                },
                {
                    "type": "languages",
                    "label": "Collaborator required language(s)"
                }                
            ]
        },
        orderOfTagTypes() {
            return [
                'roles',
                'populations',
                'environments',
                'interests',
                'languages'
            ]
        },
        selectedProfiles() {
            return this.$stores.profiles.getProfilePreviewsByIds(this.selectedProfileIds)
        },
        allProfileEntities: function () {
            return this.$stores.profiles.getEntities()
        },
        profilesWithMatchingTags: function() {

            let allProfiles = this.allProfiles
            if (this.tagsToFind.length == 0 && (this.countryFilter === '' || this.countryFilter === null)) {
                return []
            }

            if (this.countryFilter && this.countryFilter !== '') {
                allProfiles = allProfiles.filter(profile => {
                    return profile.geographic_location && profile.geographic_location.country && profile.geographic_location.country.code === this.countryFilter
                })
            }
                    

            if (this.tagsFilteringMode == 'matching-any') {
                return this.allProfiles.filter(profile => {
                        if (!profile.tags_with_type || !Array.isArray(profile.tags_with_type)) {
                            return false
                        }
                        return profile.tags_with_type.some(tag => this.tagsToFind.includes(tag.tag_id))
                    })
            }

            if (this.tagsFilteringMode == 'matching-all') {
                return allProfiles.filter(profile => {
                        if (!profile.tags_with_type || !Array.isArray(profile.tags_with_type)) {
                            return false
                        }
                        const profileTagIds = profile.tags_with_type.map(tag => tag.tag_id)
                        return this.tagsToFind.every(tagId => profileTagIds.includes(tagId))
                    })
            }
       },
        allProfiles() {
            return this.$stores.profiles.profilePreviews
        }
    },
    components: {
        profileFinder
    },
    props: {
        submission: {
            type: Object,
            required: true
        }
    }
}

</script>
