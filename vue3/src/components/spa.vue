<template>
    <div>

        <header class="mb-0">

            <nav-header 
                :page-mode="'singlePage'"
                />

            <hr class="d-block d-md-none mt-0 mb-0">
        </header>

        <main class="pb-5 pb-sm-0 d-flex main-content">
            <!--

            <nav-side-bar class="d-none side-bar-container sticky-top position-fixed" />
                -->

            <div class="w-100 main-view-container">
                
                <router-view v-slot="{ Component }">
                    <keep-alive>
                        <component :is="Component" />
                    </keep-alive>
                </router-view>

            </div>

        </main>

        <div v-if="$route.meta.byPassAuthentication || this.$route.meta.hideHeader">

        </div>
        <footer class="container" v-else>
            <hr style="margin-top:100px;">

            <div class="text-center py-4 footer-inner" v-if="$ui.showPoweredBy">
    
                <span class="small">Read our <a href="/en/policies/toc/" class="" target="_blank"><strong>Terms and Conditions</strong></a> and <a href="/en/policies/privacy/" target="_blank"><strong>Privacy Policy</strong></a>.</span>
            </div>
            
            <div class="text-center footer-logo" v-if="$ui.showPoweredBy">
    
            </div>


        </footer>

    </div>

</template>

<style>

.footer-logo {
    margin-bottom:100px;
}

.footer-logo img {
    height:100px;
}

.footer-inner {
    opacity:0.9;
    margin-top:20px;
    margin-bottom:0px;
}



.side-bar-container {
    margin-top:70px;
}

.main-view-container {
/*     overflow-y: scroll;
    overflow-x:scroll;
    height:100%;

    margin-left:270px;
    */

}

main {
    display:flex;
}

.main-content {
    margin-top:70px;
}

</style>

<script>

import navHeader from './header/nav-header.vue'

export default {
    name: 'spa',
    components: {
        navHeader
    },
    directives: {
    },
    methods: {
        loadMore() {
            console.log('load more')
        }
    },
    mounted: function() {
        const timeOfPageLoad = new Date().getTime()
        this.$router.beforeEach((to, from, next) => {

            this.$stores.events.addEvent('new-page-navigation', to.name)
//            this.$root.eventBus.$emit('new-page-navigation', to.name)
            let timeSincePageLoad = new Date().getTime() - timeOfPageLoad
            let secondsToKeepPageAlive = 60 * 10
            if (timeSincePageLoad > secondsToKeepPageAlive * 1000) {
                window.location = to.fullPath
                return false
            } else {
                next()
            }

        })
    },
}

</script>