<template>
    
    <div class="container-fluid bg-white pt-0 pb-4" style="min-height:500px;">

        <div class="row pb-4">
            <div class="col-lg-7 offset-lg-2 mb-3">

                <platform-identifier class="mt-4 patform-identifier"></platform-identifier>

                <onboarding :access-code="$route.params.token" />

            </div>
        </div>
    </div>
</template>

<style>

.community-heading {
    padding-left:0px!important;
}

</style>

<script>
import platformIdentifier from '../components/platform-identifier.vue'
import onboarding from '../components/profiles/onboarding.vue';
import signInCode from '../components/authentication/sign-in-code.vue'
export default {
    name: 'access-page',
    components: {
        signInCode,
        onboarding,
        platformIdentifier
    }
}

</script>