<template>

    <div class="container-fluid bg-white pt-1 pb-4" style="min-height:500px;">

        <div class="row pb-4">
            <div class="col-lg-7 offset-lg-2 mb-3">

                <onboarding></onboarding>

            </div>
        </div>

    </div>


</template>

<script>
import Onboarding from '../components/profiles/onboarding.vue';
export default {
    name: 'OnboardingPage',
    components: {
        Onboarding
    },
    watch:{

    },    
}

</script>