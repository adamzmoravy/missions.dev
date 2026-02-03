<template>
    <div class="container">
        <div class="row mt-5 pt-5">
            <div class="col-md-3">

            </div>
            <div class="col-md-6">
                <platform-identifier class="patform-identifier"></platform-identifier>
                
                <h3 class="mt-3 mb-4" v-html="$trans('access_page_explainer') + $trans('access_page_explainer_2')"></h3>
 
                <ui-button @click="uiStore.modal = 'login-modal'" :size="'md'" :type="'new'" :label="$trans('Login')" ></ui-button>

                <p class="mt-3 mb-4" v-if="$config.platform.site_registration_enabled">Don't have an account yet? <ui-a :href="'/onboarding/'" class="underline">Register here</ui-a>.</p>

            </div>
            <div class="col-md-3">

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
    import platformIdentifier from './platform-identifier.vue'
    import { useUIStore } from '../stores/ui'
    import { mapStores } from 'pinia'
    export default {
        name: 'platform-not-accessible',
        components: {
            platformIdentifier
        },
        computed: {
            ...mapStores(useUIStore)
        }
    }

</script>