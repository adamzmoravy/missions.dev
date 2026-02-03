<template>
    <div>
        <div class="container-fluid page-section">
            <div class="container px-0 px-md-2 py-4">
                <h1 class="x-large mt-2 text-center">

                    <strong>{{ $config.platform.site_title }}</strong>
                </h1>

                <div class="row mt-4 pt-4 mx-auto" style="max-width:700px;">
                    <div class="col-md-7 col-12">
                        <p class="mb-4 large">

                            <ui-text-fitter base-length="217" :min-size-percent="80" :text="$config.platform.mission_statement">
                                                        
                            </ui-text-fitter>
                        </p>
                        
                        <div class="mt-3">
                            <ui-button size="lg" class="d-none mr-3">Read more</ui-button>
                            <ui-button v-if="!$isUserAuthenticated" :href="'/onboarding/'" button-type="action" size="lg">→ {{ $trans('Join_welcome_page') }}</ui-button>
                        </div>


                    </div>
                    <div class="col-md-5 ">
                        <div class="d-none d-md-flex align-items-center h-100">
                            <img :src="$ui.missionImage" class="mission-image">
                        </div>

                    </div>


                </div>

            </div>
            <div class="container d-none">
                <div class="row">

                    <div class="col-md-6 offset-md-1 py-5">

                        <h1 class="large">
                            <strong>
                                {{ $config.platform.site_title }}
                            </strong>

                        </h1>  

                        <p class="medium-2 mb-2 ml-2 mt-4"><strong>Mission:</strong></p>


                            <p class="large border mt-0 mb-4 p-3 mission-statement">
                                {{ $config.platform.mission_statement }}
                            </p>


                            <ui-button size="lg" class="mr-3 ml-2">Read more</ui-button>
                            <ui-a :href="'/onboarding/'"><ui-button button-type="action" size="lg">→ Join</ui-button></ui-a>


                    </div>
                    <div class="py-5 d-flex justify-content-center">
                        <img :src="'/img/mission.svg'" class="mission-image">
                    </div>

                </div>
            </div>
        </div>

        <div class="container-fluid page-section" v-if="submissions.length > 0">
            <div class="container py-5">
                <div class="ml-3">
                    <h3 class="large mt-2 mb-1">{{$trans('Submissions')}}</h3>
                    <h4 class="medium mb-4">{{ $trans('submissions_subtitle') }}</h4>

                </div>



                        <vue-horizontal v-if="submissions.length > 0">
        
                                <template v-for="experiment in submissions">
                                    <submission-preview :experiment="experiment" />
                                </template>
        
                        </vue-horizontal>

                        <div class="d-flex justify-content-center"><div class="medium-2 my-3"> → <ui-a :href="/ideation/" class="underline">{{$trans('Browse all submissions')}}</ui-a></div></div>
          
            </div>

        </div>



        <div class="container-fluid page-section" v-if="spaces.length > 0">
            <div class="container py-5">

                <div class="ml-3">

                    <h3 class="large mt-2 mb-3">{{ $trans('Spaces') }}</h3>
                    <h4 class="medium mb-4">{{$trans('spaces_subtitle')}}</h4>
                </div>

                    <div v-if="spaces.length > 0" class="container-fluid" >
                        <div class="px-0 px-sm-2">
                            <div class="d-flex align-content-center align-items-center">
                                <div class="d-block mx-auto" style="max-width:100%;">
                                    <vue-horizontal v-if="spaces.length > 0"  style="height:350px;">
                                        <space-preview :size="300" v-for="space in spaces" :space="space" :key="space.space_id" />
                                    </vue-horizontal>
                                </div>
                            </div>
                        </div>


                    </div>

            </div>

        </div>


        <div class="container-fluid page-section">
            <div class="container py-5">
                <div class="ml-3">
                    <h3 class="large mt-2 mb-1">{{ $trans('Groups') }}</h3>
                    <h4 class="medium mb-4">{{$trans('groups_page_explainer')}}</h4>

                </div>

                <vue-horizontal v-if="groups.length > 0">

                    <group-preview class="col-md-6 col-lg-4 pl-2"
                    v-for="group in groups" :happening="group" :key="group.happening_id" />

                </vue-horizontal>

          
            </div>

        </div>


    </div>
</template>

<style scoped>

.page-section:nth-child(odd) {
  background:white;

}
.mission-image {
    width:200px;
    margin-left:40px;
    margin-bottom:20px;

}

.mission-statement {
    border-radius: 20px;
}
</style>


<script>
import groupPreview from '../components/groups/group-preview.vue'
import { UsePreferredLanguages } from '@vueuse/components'

import spacePreview from '../components/canvases/space-preview.vue';

export default {
    name: 'WelcomePage',
    data() {
        return {
            message: 'Welcome to my website!'
        }
    },
    components: {
        spacePreview,
        UsePreferredLanguages,
        groupPreview
    },
    computed: {
        spaces() {
            return this.$stores.spaces.spaces
        },
        submissions () {
            return this.$stores.submissions.getSortedSubmissions([{
                sorter: 'relevance',
                direction: 'desc',
            }])
        },
        groups() {
            return this.$stores.groups.groups
        }
    },
    mounted() {
        if (this.$ui.frontPage == 'welcome-landing') {
            this.$router.push('/' + this.$lang + '/landing/')
        }
        else if (this.$stores.auth.isAuthenticated) {
            this.$router.push('/' + this.$lang + '/feed/')
        }
    }
}
</script>

