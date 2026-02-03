<template>
    <div>
    </div>
</template>

<script>
export default {
    name: 'FrontPage',
    watch: {
        $ui: {
            handler(newVal, oldVal) {
                if (newVal != null) {


//                    this.redirect()

                }
            },
            immediate: true
        },
        $route (to, from){
            if (to.name == 'fronter') {
                this.redirect()
            }
        }
    },
    methods: {
        redirect: function() {

            if (this.$isUserAuthenticated) {
                    this.$router.replace({
                    path: '/' + this.$lang + '/' + this.$ui.frontPage + '/'
                    })
                } else {
                    this.$router.replace({
                    path: '/' + this.$lang + '/welcome/'
                    })

                }                    
            }
    },
    mounted: function() {
        this.redirect()
    }

}
</script>

