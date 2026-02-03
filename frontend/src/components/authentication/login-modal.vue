<template>

        <ui-modal :title="$trans('Logging in')" :open="$stores.ui.modal == 'login-modal'" @close="$emit('close')">

            <template #body>

                <div v-if="!isUserAuthenticated" class="w-100 d-flex justify-content-center mx-0 px-0 w-100">
                    <div class="login-process-content w-md-auto col-12 col-md-8">
                        <div class="pt-4">
                            <label for="email" class="medium-2 mb-2">{{$trans('your_email')}}:</label>
                            <input :disabled="userExists !== null" @keyup.enter="checkIfUserExists()" v-model="credentials.email" type="email" id="email" class="form-control form-control form-control-lg" style="max-width:400px;" placeholder="example@example.com" ref="initialEmail">

                            <div class="mt-2 pt-2" v-if="userExists" style="max-width:400px;">
                                <p class="d-none mt-2 medium"><strong>{{$trans('welcome_back')}}</strong></p>
                                <label for="email" class="medium">{{$trans('please_login_with_pass')}}:</label>
                                <input type="password" autofocus autocomplete="current-password" v-on:keyup.enter="login()" name="password" class="form-control form-control form-control-lg" style="width:100%;" :placeholder="$trans('Password')" v-model="credentials.password">    
                                <p><ui-a :href="'/password-reset/'" style="float:right;"> {{$trans('I forgot my password')}}</ui-a></p>
                            </div>
                        </div>
                        <div v-if="userExists === false && !registrationEnabled" class="mt-2 pt-2">

                            <p class="mt-2 medium-2">{{$trans('E-mail address not found.')}}</p>
                            <p class="medium">{{$trans('If you are allready registered, you may have entered the wrong e-mail address.')}}</p>
                            <p class="large underline"><a @click="userExists = null">Try again</a>.</p>

                        </div>
                        <div class="mt-2 pt-2" v-else-if="userExists === false && registrationEnabled">
                                <div>
                                    <p class="mt-2 medium-2">{{$trans('welcome_new')}}</p>
                                    <label for="email" class="medium">{{$trans('Your name')}}</label>
                                    <div class="pb-4 d-md-flex">
                                        <input type="text" style="max-width:200px;" name="first_name" class="d-inline form-control form-control-lg" :placeholder="$trans('First name')" v-model="credentials.first_name">
                                        <input type="text" style="max-width:250px;" name="last_name" class="d-inline mt-2 mt-md-0 ml-md-2 form-control form-control-lg" :placeholder="$trans('Last name')" v-model="credentials.last_name">

                                    </div>

                                    <div>
                                    </div>
                                    <div>
                                    </div>

                                    <label for="email mt-4" class="medium">{{$trans('create_password')}}:</label>
                                    <input v-on:keyup.enter="validateForm()" autocomplete="new-password" type="password" name="password" class="form-control form-control form-control-lg" style="max-width:400px;" :placeholder="$trans('New Password')" v-model="credentials.password">    

                                    <p class="mt-4 small-2" style="max-width:500px;" v-if="$ui.terms">By signing up, you agree to the <span v-if="$ui.terms.code_of_conduct"><a :href="$ui.terms.code_of_conduct.url" class="underline" target="_blank">{{ $ui.terms.code_of_conduct.label }}</a>, as well as the</span>
                                        Mission.dev <a :href="$ui.terms.terms_and_conditions.url" class="underline" target="_blank">{{ $ui.terms.terms_and_conditions.label }}</a> and <a :href="$ui.terms.privacy_policy.url" class="underline" target="_blank">{{ $ui.terms.privacy_policy.label }}</a>.</p>


                                </div>
                        </div>
                        
                    </div>


                </div>
                <div v-else>
                    <p class="large">{{$trans('You are already logged in')}}</p>
                </div>

            </template>
            <template #footer>
                <div class="d-flex justify-content-center">

                        <template v-if="userExists === null">
                            
                            <ui-button @click="checkIfUserExists()" :label="$trans('Continue')" />
                        </template>
                        <template v-else-if="userExists === true">
                            <ui-button 
                                :working="loggingIn"
                                class="mr-3"
                                :label="$trans('Log in')"
                                id="modal-login-submit"
                                button-type="new"
                                @click="login()">
                            </ui-button>

                        </template>
                        <template v-else-if="userExists === false && registrationEnabled">
                            <ui-button 
                                class="mr-3"
                                :label="$trans('Register')"
                                id="modal-login-submit"
                                button-type="new"
                                @click="register()">
                            </ui-button>

                        </template>

                </div>

            </template>

        </ui-modal>

</template>

<style scoped>

.login-process-content {
}


</style>

<script>
import backend from '../../backend.js'

import { useAuthStore } from '../../stores/auth'
import { useProfilesStore } from '../../stores/profiles'
import { mapStores } from 'pinia'

import signUp from './sign-up.vue'
import { onMounted } from 'vue'

export default {
    name: 'login-modal',
    data() {
        return {
            credentials: {
                email: '',
                password: '',
                first_name: '',
                last_name: ''
            },
            userExists: null,
            errorMessage: false,
            showErrors: false,
            loggingIn: false
        }
    },
    components: {
        signUp
    },
    computed: {
        registrationEnabled: function () {
            return this.$config.platform.site_registration_enabled
        },
        platformIsPrivate: function () {
            return this.$config.platform.access.site == 'members_only'
        },
        ...mapStores(useAuthStore, useProfilesStore)
    },
    props: {
        redirectPath: { /* Optional. Path to redirect to if sign in successful */
            required: false,
            default: false
        }
    },
    mixins: [

    ],
    watch: {
    },
    methods: {
        register: function() {
            this.credentials.email = this.credentials.email.toLowerCase()
            this.$stores.auth.register(this.credentials).then(response => {
                if (response.data.status == 'success') {
                    this.$router.push('/' + this.$lang + '/onboarding/')
                    this.$stores.auth.setToken(response.data.token.string)
                    this.$stores.ui.modal = null
                }

            }).catch(error => {
                alert('Error registering')
            })

        },
        checkIfUserExists: function (e) {
            let urlEncodedEmail = encodeURIComponent(this.credentials.email)
            console.log('check')
            backend.get('/users/identifier/', null, {
                email: urlEncodedEmail
            }).then((response) => {
                this.userExists = response.data.exists
            })
        },
        close: function () {
            this.$emit('close')
        },
        login: function () {
            this.loggingIn = true
            backend.post('/users/tokens/', this.credentials)
            .then(response => {
                this.authStore.setToken(response.data.token.string)
//                this.$stores.events.addEvent('user-login', {})
                this.loggingIn = false                
                this.$stores.ui.modal = null


            }).catch(error => {
                console.log(error)
            })
        },
        validateForm: function () {
            console.log('validate form')
        },
        onMounted() {
            this.$refs.initialEmail.focus()
        }
    }
}
</script>
