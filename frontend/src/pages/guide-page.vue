<template>

    <guide ref="guide"></guide>    

</template>

<script>
import guide from '../components/guide.vue'
export default {
    name: 'GuidePage',
    components: {
        guide
    },
    watch:{
        $route (to, from){
            if (to.name == 'guide') {
                this.$refs.guide.setup()
            }
        }
    }
}

</script>