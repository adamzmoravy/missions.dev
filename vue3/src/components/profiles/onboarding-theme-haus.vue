
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
                    {{$trans('onboarding_haus_start_1')}}
                </p>
                <p class="large">
                    {{$trans('onboarding_haus_start_2')}}
                </p>
                <p>
                    <button @click="$parent.setStep = 'profile-wizard'" type="button" name="button" class="mb-2 wizard-button continue-button">
                    <span class="black strong">→ {{$trans('Lets do it')}}!</span>
                    </button>

                </p>

            </div>


            <div v-if="$parent.step == 'profile-wizard'">
                <profile-wizard :stages="$ui.onboarding.haus.steps" @finished="profileWizardFinished"></profile-wizard>

            </div>

            <div  class="mt-5 pt-4" v-if="$parent.step == 'offer-intro'">

                <p class="large">
                    {{$trans('You have completed your profile')}}.

                </p>
                <p class="large">
                    {{$trans('onboarding_start_2')}}
                </p>
                <p>
                    <button @click="$parent.setStep = 'intro'" type="button" name="button" class="mb-2 wizard-button continue-button">
                    <span class="black strong">→ {{$trans('Yes_please')}}!</span>
                    </button>
                    <br>
                    <button  @click="$parent.setStep = 'what-next'" type="button" name="button" class="wizard-button continue-button">
                    → {{$trans('No thanks')}}
                    </button>

                </p>
            

            </div>


            <div v-if="$parent.step == 'intro'">

                <platform-intro @finished="$parent.setStep = 'what-next'"></platform-intro>

            </div>

            <div  class="mt-5 pt-4" v-if="$parent.step == 'what-next'">

                <p class="large">
                    {{$trans('Well done')}}! {{$trans('you_finished_onboarding')}}.
                                </p>

                <p class="large">
                    {{$trans('what_do_now')}}

                  <ul class="mt-2" v-html="$trans('onboarding_well_done_now_what') "></ul>
                </p>
            

            </div>

        </div>
    </div>

</template>



<script>
    import profileWizard from './profile-wizard.vue'
    import signIn from '../authentication/sign-in.vue'
    import platformIntro from './platform-intro-v2.vue'
    export default {
        name: 'onboarding-theme',
        components: {
            signIn,
            profileWizard,
            platformIntro
        },
        methods: {
            profileWizardFinished: function () {
                if (this.$ui.showPlatformIntro) {
                    this.$parent.setStep = 'offer-intro'
                    return
                }
                this.$parent.setStep = 'what-next'
            }
        }
    }
</script>
