<template>
    <div>

        <profile-of-user v-if="$route.params.profileId" :key="$route.fullPath" :profile-id="$route.params.profileId"></profile-of-user>

    </div>

</template>

<script>
import ProfileOfUser from '../components/profiles/profile-of-user.vue'
export default {
    name: 'ProfilePage',
    data() {
        return {
            limit: 24
        }
    },
    components: {
        ProfileOfUser
    },
    props: {
    },
    methods: {

    }
}

</script>