<template>


        <div v-if="group" style="background-color:white;justify-content:space-between;" class="my-3 comment-container p-3 d-md-flex">
                    <div>
                        <h3>{{ groupName }}</h3>

                    
                        <div class="d-flex align-items-center" v-if="group.registrations.length">

                            <span class="material-symbols-outlined md-18">emoji_people</span>

                            <span>{{group.registrations.length}} participating</span>
                        </div>
                    </div>

                    <div  style="flex-grow: 1;max-width:350px;" class="pl-md-4 ml-4">


                    <profile-index-row
                        class="w-100"
                        :profiles="$stores.profiles.getProfilePreviewsByIds(group.registrations)"
                        :profile-size="40"
                        :max-show-num-profiles="15"
                        :show-name="false"
                        @show-all-profiles="showProfileModal = event.event_id"
                        @profiles-fit=""
                    />



                    </div>
                    <div>


                        <div class="d-flex justify-content-end"> 

                                <ui-button v-if="!currentUserIsRegistered" :href="'/group/' + happeningId" button-type="new">Join</ui-button>
                                <ui-button v-if="currentUserIsRegistered" :href="'/group/' + happeningId">View</ui-button>



                        </div>
                        <div class="w-100 pt-2" v-if="currentUserIsRegistered">You have signed up.</div>
                    </div>
    
                </div>




</template>

<style scoped>

</style>

<script>

export default {
    name: 'group-invitation',
    props: {
        happeningId: {
            required: true,
            type: String
        }
    },
    computed: {
        group: function () {
            return this.$stores.groups.getGroupPreview(this.happeningId)
        },
        currentUserIsRegistered: function () {
            if (this.group && this.$currentUser) {
                return this.group.registrations.includes(this.$currentUser.user_id)
            }
            return false
        },
        groupName: function () {
            if (this.group) {
                return this.group.name
            }
            return ''
        }        
    }
}

</script>