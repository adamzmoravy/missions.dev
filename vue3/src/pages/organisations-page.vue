<template>

    <organisations ref="organisations" />
</template>

<script>
import organisations from '../components/organisations.vue'
export default {
    name: 'OrganisationsPage',
    components: {
        organisations
    },
    watch:{
        $route (to, from){
            if (to.name == 'organisations') {
                this.$refs.organisations.setup()
            }
        }
    }
}

</script>