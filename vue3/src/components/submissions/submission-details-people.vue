<template>
    <div>
         <h2 class="small-2 mb-2">{{label}}</h2>

         <entities :edit="isUserEditor" @edit="showAddToTeamModal = true" class="on-dotted-line" style="position:relative;left:-5px;" :title="$trans('Team members')" :entities="relatedProfilesEntities" :limit="5" />

        
         <entities 
            @close="showAddToTeamModal = false" 
            v-if="showAddToTeamModal" 
            :multi-select="true" 
            :modal-only="true"                                     
            :show-search-bar="true" 
            @selected="submissionInteractor.addToTeam" 
            @unselected="submissionInteractor.removeFromTeam" 
            :multi-select-selected="teamIds" 
            :include-link="false" 
            :sub-title="$trans('Select people who should be able to edit this submission.')"
            :title="$trans('Edit people')" 
            :entities="allProfileEntities" 
            :limit="5000" />


    </div>
</template>

<style scoped>

.people-container {
    margin-left: -1px;
}

</style>


<script>

export default {
    name: 'submission-details-people',
    data () {
        return {
            showProfileModal: false,
            showAddToTeamModal: false,
            showTeamMembersNumber: 6
        }
    },
    computed: {
        allProfileEntities: function () {
            return this.$stores.profiles.getEntities()
        },
        relatedProfilesEntities: function () {
            return this.$stores.profiles.getEntities([], [{
                selection: 'ids',
                value: this.teamIds
            }])
        }
    },
    props: {
        label: {
            required: false,
            default: 'Team'
        },  
        isUserEditor: {
            type: Boolean,
            required: false,
            default: false
        },
        submission: {
            type: Object,
            required: false
        },
        submissionInteractor: {
            type: Object,
            required: false
        },
        teamIds: {
        }
    }   
}

</script>
