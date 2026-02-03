<template>

    <group v-if="happeningId" ref="happening" :start-mode="startMode" :key="$route.fullPath" :happening-id="happeningId" />


</template>

<script>

import group from '../components/groups/group.vue'

export default {

    name: 'GroupPage',
    props: {
        startMode: {
            required: false,
            default: 'show-happening'
        }
    },
    computed: {
        happeningId () {
            if (this.$route.params.groupId) {
                return this.$route.params.groupId
            }
            if (this.$stores.platform.config.groups.default) {
                return this.$stores.platform.config.groups.default
            }
            return false 
        }
    },
    components: {
        group
    },
    watch:{
        $route (to, from){
            if (to.name == 'home') {
                // do this after 2 seconds
                setTimeout(() => {
//                    this.$refs.happening.setup()
                }, 1000);
            }
        }
    }

}

</script>