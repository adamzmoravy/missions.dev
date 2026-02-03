<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
import auth from '../mixins/auth.js'
import { useAuthStore } from '../stores/auth'
import { useProfilesStore } from '../stores/profiles'
import { mapStores } from 'pinia'


export default {
    name: 'sign-out',
    data() {
        return {}
    },
    props: {
        redirectPath: { /* Optional. Path to redirect to if sign out successful */
            required: false,
            default: false
        }
    },
    computed: {
        ...mapStores(useAuthStore, useProfilesStore)
    }, 
    mixins: [
        auth,
        helpers
    ],
    methods: {
        logout: function() {
            this.profilesStore.currentUser = null
            this.authStore.removeToken()
            this.$emit('closed')
        },
        validateForm: function() {
            // dispatch event so that authorization can process the logout
            this.$root.eventBus.$emit('doLogout', this.redirectPath)
            // close the logout modal
            this.$emit('close')
        }
    }

}
</script>
