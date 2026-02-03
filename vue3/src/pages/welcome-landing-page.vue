<template>

</template>

<style scoped>

.different img {
    height:220px;
}

.very-large {
 font-size:2.2em!important;
 line-height: normal;
}

.page-section:nth-child(odd) {
  background:white;

}

.mission-image {
    width:200px;
    margin-left:40px;
    opacity:0.5;
}

.mission-statement {
    border-radius: 20px;
}

</style>


<script>

import PlatformStats from '../components/platform-stats.vue'
import backend from '../backend.js'
import UseCases from '../components/use-cases.vue'
export default {
    name: 'WelcomeLandingPage',
    data() {
        return {
            message: 'Welcome to my website!',
            allPublicPlatformsProfiles: []
        }
    },
    components: {
        PlatformStats,
        UseCases
    },
    computed: {
        visibleProfiles: function () {
            return this.allPublicPlatformsProfiles.filter(profile => profile.image !== null).slice(0, 180)
        }
    },
    methods: {
        randomizeArray (array) {
            let currentIndex = array.length, temporaryValue, randomIndex

            // While there remain elements to shuffle...
            while (0 !== currentIndex) {

                // Pick a remaining element...
                randomIndex = Math.floor(Math.random() * currentIndex)
                currentIndex -= 1

                // And swap it with the current element.
                temporaryValue = array[currentIndex]
                array[currentIndex] = array[randomIndex]
                array[randomIndex] = temporaryValue
            }

            return array
        },
        getAllPublicPlatformsProfiles: function () {
            backend.get('/all_platforms/users/preview/').then((response) => {
               let profiles = response.data.previews               
               this.allPublicPlatformsProfiles = this.randomizeArray(profiles)
            })            
        },
    },
    mounted() {

        this.getAllPublicPlatformsProfiles()

    }
}
</script>


