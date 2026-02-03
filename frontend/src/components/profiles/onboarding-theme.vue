<template>

    <div>
        <div>
            <sign-in class="mt-5" v-if="$parent.step == 'sign-in'" :access-code="$parent.accessCode">

            </sign-in>

            <div class="mt-5 pt-4" v-if="$parent.step == 'first-step'">

                <p class="large">
                    {{$trans('Hi')}} {{ $currentUser.first_name }}!
                </p>
                <p class="large">
                    {{$trans('onboarding_start_1')}}
                </p>
                <p class="large">
                    {{$trans('onboarding_start_2')}}
                </p>
                <p>
                    <button @click="$parent.setStep = 'intro'" type="button" name="button" class="mb-2 wizard-button continue-button">
                    <span class="black strong">→ {{$trans('Yes_please')}}!</span>
                    </button>
                    <br>
                    <button  @click="finishIntro" type="button" name="button" class="wizard-button continue-button">
                    → {{$trans('No thanks')}}
                    </button>

                </p>

            </div>

            <div v-if="$parent.step == 'intro'">

                <platform-intro @finished="finishIntro"></platform-intro>

            </div>


            <div class="mt-5 pt-4" v-if="$parent.step == 'offer_platform_wizard'">

                <p class="large">
                    You are an admin of this platform.
                </p>
                <p class="large">
                    In the next step you can customise it to suit your community.
                </p>
                <p>
                    <button @click="$parent.setStep = 'platform-wizard'" type="button" name="button" class="mb-2 wizard-button continue-button">
                    <span class="black strong">→ {{$trans('Lets do it')}}</span>
                    </button>
                    <br>
                    <button  @click="$parent.setStep = 'offer_profile_wizard'" type="button" name="button" class="wizard-button continue-button">
                    → {{$trans('Maybe later')}}
                    </button>

                </p>

            </div>

            <div v-if="$parent.step == 'platform-wizard'">
    
                <admin-platform-wizard @finished="$parent.setStep = 'offer_profile_wizard'"></admin-platform-wizard>

            </div>


            <div class="mt-5 pt-4" v-if="$parent.step == 'offer_profile_wizard'">

                <p class="large">
                    {{$trans('profile_ask')}}
                </p>
                <p class="large">
                    {{$trans('profile_community')}}
                </p>
                <p>
                    <button @click="$parent.setStep = 'profile-wizard'" type="button" name="button" class="mb-2 wizard-button continue-button">
                    <span class="black strong">→ {{$trans('Lets do it')}}</span>
                    </button>
                    <br>
                    <button  @click="finishedProfileWizard()" type="button" name="button" class="wizard-button continue-button">
                    → {{$trans('Maybe later')}}
                    </button>

                </p>

            </div>


            <div v-if="$parent.step == 'profile-wizard'">

                <profile-wizard @finished="finishedProfileWizard()"></profile-wizard>

            </div>



            <div  class="mt-5 pt-4" v-if="$parent.step == 'what-next'">

                <p class="large">
                    {{$trans('Well done')}} <strong>{{ userFirstName }}</strong>! {{$trans('you_finished_onboarding')}}.
                </p>
                <p class="large">
                    {{$trans('what_do_now')}}
                </p>                    
                <div class="row">
                    <div class="col-md-4 py-0 px-4">
                        <ui-a :href="'/home/'" class="pb-2 nav-header-button">
                            <span class="nav-header-icon px-3">
                                <img :src="'/img/home-outline-thinner.svg'" style="height: 70%; margin-top: 20%;">
                            </span> 
                            <span class="d-sm-flex large">{{$trans('Home')}}</span>
                            <span class="d-sm-flex text-center">{{$trans('home_explainer')}}</span>
                        </ui-a>                                                        
                    </div>
                    <div class="col-md-4 py-0 px-4">
                        <ui-a href="/submissions/" class="pb-2 nav-header-button">
                            <span class="nav-header-icon px-3">
                                <img :src="'/img/hex-outline-multi.svg'" style="height: 60%; margin-top: 30%;">
                            </span> 
                            <span class="d-sm-flex large">{{$trans('Submissions')}}</span>
                            <span class="d-sm-flex text-center">{{$trans('submissions_explainer')}}</span>
                        </ui-a>                                                        
                    </div>
                    <div class="col-md-4 py-0 px-4">
                    
                        <ui-a href="/people/" class="pb-2 nav-header-button">
                            <span class="nav-header-icon px-3">
                                <img :src="'/img/group-outline-thinner.svg'" style="height: 70%; margin-top: 30px;">
                            </span> 
                            <span class="d-sm-flex large">{{$trans('People')}}</span>
                            <span class="d-sm-flex text-center">{{$trans('people_explainer')}}</span>
                        </ui-a>                                                        
                    </div>

                </div>

            </div>

        </div>
    </div>

</template>


<script>
    import platformIntro from './platform-intro.vue'
    import profileWizard from './profile-wizard.vue'
    import signIn from '../authentication/sign-in.vue'
    import adminPlatformWizard from '../../components/admin/admin-platform-wizard.vue'
    export default {
        name: 'onboarding-theme',
        methods: {
            finishIntro() {
                if (this.$currentUser.user_type == 'admin') {
                    this.$parent.setStep = 'offer_platform_wizard'
                } else {
                    this.$parent.setStep = 'offer_profile_wizard'
                }
            },
            finishedProfileWizard() {
                this.$parent.setStep = 'what-next'  
            }
        },
        components: {
            signIn,
            profileWizard,
            platformIntro,
            adminPlatformWizard
        }
    }
</script>