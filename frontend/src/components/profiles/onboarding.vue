<template>

    <div>
        <component :is="$theme('onboarding')"></component>
        
    </div>

</template>

<script>
export default {
    mixins: [
    ],
    name: 'onboarding',
    computed: {
        step: function () {
            if (!this.isUserAuthenticated) {
                return 'sign-in'
            }   
            return this.setStep
        }
    },
    props: {
        accessCode: {
            required: false,
            default: null
        }
    },
    data() {
        return {
            setStep: 'first-step'
        }
    }
}
</script>