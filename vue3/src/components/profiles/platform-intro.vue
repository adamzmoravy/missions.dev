<template>

    <div class="row pt-4">

        <div class="col-md-2 mt-4" style="height:80px;">

            <span v-if="stage==0" href="/en/submissions/" class="pb-2 nav-header-button">
                <span class="nav-header-icon">
                    <img :src="'/img/hex-outline-multi.svg'" style="height: 90%; margin-top: 2px;">
                </span> 
                <span class="medium selected-element d-sm-flex">{{$trans('Submissions')}}</span>
            </span>

            <span v-if="stage==4" href="/en/submissions/" class="pb-2 nav-header-button">
                <span class="nav-header-icon">
                    <img :src="'/img/group-outline-thinner.svg'" style="height: 100%; margin-top: 2px;">
                </span> 
                <span class="medium selected-element d-sm-flex">{{$trans('People')}}</span>
            </span>


        </div>

        <ui-steps @finished="$emit('finished')" class="col-lg-10 pt-4" v-slot:default="slotProps" :stages="[
            {
                'name': 'submissions'
            },
            {
                'name': 'type_explainer'
            },
            {
                'name': 'feedback'
            },
            {
                'name': 'take_part'
            },
            {
                'name': 'people'
            }


        ]" @stage-changed="stage = $event">



            <div v-if="slotProps.stageName == 'submissions'" class="pt-4 pl-5 pr-3">

                <p class="medium-2 pb-3" v-html="$trans('platform_intro_submissions')">
                </p>

                    <ui-stage-process-explainer icon-type="hexagon" style="max-width:300px;height:100px;" />

            </div>


            <div v-if="slotProps.stageName == 'type_explainer'" class="pt-4 pl-5 pr-3">

                <p class="medium-2 pb-2" v-html="$trans('types_submissions')">
                </p>
                <p>
                    <div class="d-flex align-items-center">
                        <submission-type stage="0" class="mr-3"></submission-type>
                        <span class="medium-2">
                            <strong>{{$trans('Challenges')}}</strong>: {{$trans('challenges_explainer')}}
                        </span>
                    </div> 
                </p>
                <p>
                    <div class="d-flex align-items-center">
                        <submission-type stage="1" class="mr-3"></submission-type>
                        <span class="medium-2">
                            <strong>{{$trans('Ideas')}}:</strong> {{$trans('ideas_explainer')}}
                        </span>
                    </div> 
                </p>
                <p>
                    <div class="d-flex align-items-center">
                        <submission-type stage="2" class="mr-3"></submission-type>
                        <span  class="medium-2">
                            <strong>{{$trans('Projects')}}</strong>: {{$trans('projects_explainer')}}
                        </span>
                    </div> 
                </p>

            </div>

            <div v-if="slotProps.stageName == 'feedback'" class="pt-0 pl-5 pr-3 mb-4">
                <p class="medium-2 mt-4" v-html="$trans('feedback_explainer_1')">
                </p>
                <p v-html="$trans('feedback_explainer_2')"></p>
                <p>
                    <div v-if="true" class="d-flex justify-content-between w-100" style="max-width:500px;">
                        <div v-for="(hat, i) in hats" class="hat-selection" style="cursor:auto;:center;">

                            <div style="height:70%;margin:auto;" :class="hat.color + '-hat-bg'" class="hat"></div>
                            <span class="color-explainer small" :class="hat.color">{{$trans(hat.meaning)}}</span>

                        </div>
                    </div>
                </p>

            </div>
                

            <div v-if="slotProps.stageName == 'take_part'" class="pt-4 pl-5 pr-3 mb-4">
                <p class="medium-2" v-html="$trans('reactions_explainer')">
                    
                </p>
                <div class="care-about-container pt-2 d-flex w-100 justify-content-between">
                    <div class="care-about-icon">
                        <span class="w-100  d-flex justify-content-center">
                            <img :src="'/img/heart-active.svg'" class="m-auto">
                        </span>
                        <span class="d-flex mt-2 justify-content-center">
                            {{$trans('I care about this')}}
                            </span>
                    </div>

                    <div class="care-about-icon">
                        <span class="w-100  d-flex justify-content-center">
                            <img :src="'/img/news-active.png'" class="m-auto">
                        </span>
                        <span class="d-flex mt-2 justify-content-center">
                           {{$trans('Id like updates')}}
                            </span>
                    </div>
                    <div class="care-about-icon">
                        <span class="w-100  d-flex justify-content-center">
                            <img :src="'/img/participate-active.png'" class="m-auto">
                        </span>
                        <span class="d-flex mt-2 justify-content-center">
                           {{$trans('I want to help')}}
                            </span>
                    </div>
                </div>

            </div>


            <div v-if="slotProps.stageName == 'people'" class="pl-5 pt-4 pr-3 mb-4">
                <p class="medium-2" v-html="$trans('profiles_explainer')">
                 </p>
                <p class="medium-2" v-html="$trans('groups_explainer')">
                </p>
 
            </div>

        </ui-steps>                    
    </div>
</template>

<script>
import submissionType from '../submissions/submission-type.vue'

export default {
    name: 'platform-intro',
    data() {
        return {
            stage: 0,
            ui: {
                showHats: false
            }
        }
    },
    components: {
        submissionType
    },
    colorToExplainer (color) {
        let colors = {
            'blue': this.$trans('Planning_expl'),
            'green': this.$trans('Ideas_expl'),
            'black': this.$trans('Critical_expl'),
            'white': this.$trans('Information_expl'),
            'red': this.$trans('Feelings_expl'),
            'yellow': this.$trans('Positive_expl')
        }
        return colors[color]
    },
    computed: {
        hats() {
            return this.$ui.hats
        }
    },
}
</script>

<style>
.care-about-icon {
    width:120px;
}
.care-about-icon img {
    max-height:55px;
}
.care-about-container {
    max-width:450px;
}
</style>