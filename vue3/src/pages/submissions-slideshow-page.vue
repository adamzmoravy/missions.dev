
<template>

    <div class="slideshow-container" v-if="currentSubmission">

        <div class="slideshow-caption">{{ currentSubmission.title }} </div>

        <img :src="'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_1920,e_pixelate:0,h_1080/' + currentSubmission.data.image" class="slideshow-image" />

        <div class="slideshow-team">

                                <profile-index-row
                                    class="w-100"
                                    :profiles="profiles"
                                    :profile-size="200"
                                    :max-show-num-profiles="20"
                                    :show-name="false"
                                    @profiles-fit=""
                                />


        </div>


    </div>
</template>

<style scoped>


.slideshow-team {
    display: flex;
    justify-content: flex-end;
    width:93vw;
    padding-bottom:1.5vw;
    margin:4vh;
    position:fixed;
    bottom:0;
    right:0;

}


.slideshow-caption {
    overflow: auto;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width:100vw;
    margin:4vh;
    position:fixed;
    padding:2vh;
    background-color:white;
    font-size:8vh;
}

.slideshow-image {
    width: 100vw;
    height: 100vh;

}

.slideshow-container {
    position: fixed;
    top: 0;
    right: 0;
    z-index: 1;
    width: 100vw;
    height: 100vh;
    background-color: black;
}


.hidden-profiles-number-more {
    font-size:20vh!important;
}
</style>

<style>
.slideshow-container .hidden-profiles-number-more {
    font-size:6vh!important;
}

</style>

<script>

import backend from '../backend.js'
import ProfileIndexRow from '../components/profiles/profile-index-row.vue'

export default {
    name: 'SubmissionsSlideshowPage',
    data: function () {
        return {
            selectedIndex: 3,
            secondsToChange: 6
        }
    },
    components: {
        ProfileIndexRow
    },
    computed: {
        profiles: function () {
            return this.$stores.profiles.getProfilePreviewsByIds(this.currentSubmission.data.team_ids)
        },
        currentSubmission() {
            return this.submissions[this.selectedIndex]
        },
        submissions() {
            let entities = this.$stores.submissions.getEntities([
                {
                    sorter: 'created',
                    direction: 'asc'
                }
            ],
            [{
                selection: 'stages',
                value: [9]
            }])

            return entities
        }
    },
    methods: {
        nextSlide() {
            this.selectedIndex = (this.selectedIndex + 1) % this.submissions.length
        },
        startSlideshow() {
            setInterval(this.nextSlide, this.secondsToChange * 1000)
        }
    },
    mounted() {
        this.startSlideshow()
    }
}

</script>