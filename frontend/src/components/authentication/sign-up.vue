<template>

    <div>
        <p class="mt-2 bold large">{{$trans('welcome_new')}}</p>
        <label for="email" class="medium">{{$trans('Your name')}}</label>
        <div class="pb-4">
            <input type="text" style="max-width:200px;" name="first_name" class="d-inline form-control form-control-lg" :placeholder="$trans('First name')" v-model="credentials.first_name">
            <input type="text" style="max-width:250px;" name="last_name" class="d-inline ml-2 form-control form-control-lg" :placeholder="$trans('Last name')" v-model="credentials.last_name">

        </div>

        <label for="email mt-4" class="medium">{{$trans('create_password')}}:</label>
        <input v-on:keyup.enter="register()" autocomplete="new-password" type="password" name="password" class="form-control form-control form-control-lg" style="max-width:400px;" :placeholder="$trans('New Password')" v-model="credentials.password">    

        <p class="mt-4 small-2" style="max-width:500px;" v-if="$ui.terms">By signing up, you agree to the <span v-if="$ui.terms.code_of_conduct"><a :href="$ui.terms.code_of_conduct.url" class="underline" target="_blank">{{ $ui.terms.code_of_conduct.label }}</a>, as well as the</span>
            Mission.dev <a :href="$ui.terms.terms_and_conditions.url" class="underline" target="_blank">{{ $ui.terms.terms_and_conditions.label }}</a> and <a :href="$ui.terms.privacy_policy.url" class="underline" target="_blank">{{ $ui.terms.privacy_policy.label }}</a>.</p>



            <ui-button class="mt-3" @click="register()" :label="$trans('Continue')" />

    </div>

</template>

<script>

export default {
    name: 'sign-up',
    data() {
        return {
            credentials: {
                email: ''
            },
            showErrors: false,
            signUpFinished: false,
            registeredUserId: null
        }
    },
    components: {
    },
    props: {
        email: {
            required: false,
            default: null
        },
        access: {
            required: false,
            default: 'public'
        },
        accessCode: {
            required: false,
            default: null
        },
        redirectPath: { /* Optional. Path to redirect to if sign in successful */
            required: false,
            default: false
        }
    },
    watch: {
        email: function (newVal, oldVal) {
            this.credentials.email = newVal
            console.log(newVal)
        }
    },
    mixins: [

    ],
    methods: {
        register: function() {
            this.credentials.email = this.credentials.email.toLowerCase()
            if (this.accessCode) {
                this.credentials.access_code = this.accessCode

            }
            this.$stores.auth.register(this.credentials).then(response => {
                if (response.data.status == 'success') {
                    this.$stores.auth.setToken(response.data.token.string)
                }

            }).catch(error => {
                alert('Error registering')
            })

        },
        validateForm: function() {
            this.$validator.validateAll().then(success => {
                if (success) {
                    console.log(this.credentials)
                    // debug info
                    this.debug('sign up form is valid')
                    // make sign up email not case-sensitive
                    this.credentials.email = this.credentials.email.toLowerCase()
                    // dispatch event so that authorization can process the login
                    this.$root.eventBus.$emit('doRegister', this.credentials, this.redirectPath)
                    this.signUpFinished = true
                    // close the sign in modal
                    this.$emit('close')
                } else {
                    this.showErrors = true
                    // debug info
                    this.debug('sign up form has errors')
                }
            })
        }
    },
    mounted: function() {
        if (this.email !== null) {
            this.credentials.email = this.email
        }

    }
}
</script>
