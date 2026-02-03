<template>
    <div>

        <slot :profileData="profileData" :getProfile="getProfile">

        </slot>

    </div>

</template>


<script>
    import { useProfilesStore } from '../../stores/profiles'
    import { mapStores } from 'pinia'
    import ProfileDescriptionEdit from './profile-description-edit.vue'

    export default {
        name: 'profile',
        computed: {
            ...mapStores(useProfilesStore),
            profileData() {
                return this.profilesStore.getProfileById(this.profileId)
            }
        },
        components: {
            ProfileDescriptionEdit
        },
        props: {
            profileId: {
                default: 'me'
            }
        },
        created: function() {
            this.getProfile()
        },
        methods: {
            getProfile() {
                this.profilesStore.fetchProfile(this.profileId)                
            }
        }
    }

</script>