<template>

    <div class="row pt-4">

        <div class="col-md-12 d-flex flex-column align-items-center">


            <div class="sections mb-3">
                <template v-for="item in sections" >
                        <header-menu-item 
                            class="menu-item"
                            @selected=""
                            :icon-files="$ui.iconFiles[item.icon]"
                            :active="section == sections.indexOf(item)"
                            pageMode="singlePage"
                            :name="item.page"
                            :icon="item.icon" 
                            :label="item.label"
                        />
                    </template>
            </div>
            <span v-if="false">
            Stage {{ stage }}
            Name : {{ sections[stage].name }}

            </span>


        <ui-steps @finished="$emit('finished')" class="col-lg-10 pt-3" style="min-height:250px;max-width:600px;" v-slot:default="slotProps" :stages="stages" @stage-changed="stage = $event">



            <div v-if="slotProps.stageName == 'submissions'" class="pt-4 pl-5 pr-3">

                <p class="medium-2 pb-3" v-html="$trans('platform_intro_submissions')">
                </p>

                    <ui-stage-process-explainer icon-type="hexagon" class="mx-auto" style="max-width:300px;height:100px;" />

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
                <div class="mt-3 pt-2">

                    <div class="participate-container medium">

                        <template v-for="(reaction, key) in $ui.submissionReactions">
                            <div class="reactions-field" @click="toggle(key)">
                                <div class="icon-container pr-2">
                                    <reactions-icon class="reaction-icon-component" :mode="'selected'" :reaction="key" :size="'40px'" />
                                </div>
                                <div class="participate-label mr-2 d-flex align-items-center">
                                    {{ $trans(reaction.label) }}
                                </div>
                            </div>

                        </template>

                    </div>

                </div>

            </div>

            <div v-if="slotProps.stageName == 'events'" class="pl-5 pt-4 pr-3 mb-4">

                  <p class="medium-2">
                    Use <strong>Events</strong> to organise meetups, workshops and other activities for people in the commmunity, both online and offline.
                 </p>

            </div>

            <div v-if="slotProps.stageName == 'people'" class="pl-5 pt-4 pr-3 mb-4">
                <p class="medium-2" v-html="$trans('profiles_explainer')">
                 </p>
                <p class="medium-2" v-html="$trans('groups_explainer')">
                </p>
 
            </div>



            <div v-if="slotProps.stageName == 'guide'" class="pl-5 pt-4 pr-3 mb-4">
                <p class="medium-2">
                    Look up practical things to know in the <strong>Guide</strong> section.
                 </p>
 
            </div>


        </ui-steps>         


 

        </div>
              
    </div>
</template>

<style scoped>
    .sections {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .menu-item {
        zoom:1.3;
    }


.reactions-field {
    cursor:pointer;
    display:flex;
}

.reactions-field:hover .participate-label {
    text-decoration: underline;
}

.participate-container {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-top: 1rem;
    margin-bottom: 1rem;
}


</style>

<script>
import submissionType from '../submissions/submission-type.vue'
import headerMenuItem from '../header/header-menu-item.vue'
import reactionsIcon from '../reactions-icon.vue'

export default {
    name: 'platform-intro',
    data() {
        return {
            stageToSection: {
                0: 0,
                1: 0,
                2: 0,
                3: 0,
                4: 1,
                5: 2,
                6: 3
            },
           stage: 0,
            ui: {
                showHats: false
            }
        }
    },
    components: {
        submissionType,
        headerMenuItem,
        reactionsIcon
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
    methods: {
        nextStage: function() {
            this.stage++
        },
        previousStage: function() {
            this.stage--
        }
    },
    computed: {
        stages: function() {
            return [
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
                    'name': 'events'
                },
                {
                    'name': 'people'
                },
                {   
                    'name': 'guide'
                }
            ]
        },
        section() {
            return this.stageToSection[this.stage]
        },
        showGuideForSection() {
            return this.$ui.showGuideForSection
        },
        hats() {
            return this.$ui.hats
        },
        menu: function() {
            return this.$ui.mainMenu
        },
        sections: function() {
            let menu = []
            for (let i = 0; i < this.$ui.mainMenuShow.length; i++) {
                let item = this.$ui.mainMenuShow[i]
                let menuItem = this.menu.find(menuItem => {
                    return menuItem.name == item
                })
                if (menuItem && this.showGuideForSection.includes(menuItem.name)) {
                    menu.push(menuItem)
                }
            }

            return menu
        },
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