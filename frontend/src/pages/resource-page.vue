
<template>

    <resource-details v-if="$route.params.resourceId" :key="'resource-' + $route.params.resourceId" :is-user-admin="userType == 'admin'" :resource-id="$route.params.resourceId" />

</template>

<script>
import { useAuthStore } from '../stores/auth'
import { useProfilesStore } from '../stores/profiles'
import { mapStores } from 'pinia'
import ResourceDetails from '../components/resources/resource-details.vue'
export default {
    name: 'ResourcePage',
    components: {
        ResourceDetails
    },
    computed: {
        ...mapStores(useProfilesStore, useAuthStore),
        userType() {
            return this.authStore.userData.user_type;
        }
    }

}


</script>