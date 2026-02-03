<template>

    <div>
        <div v-if="!isUserAuthenticated">
            <div>
                <form @submit.prevent="" novalidate>
                    <label for="email" class="medium">{{$trans('your_email')}}:</label>
                    <input v-on:keyup.enter="checkIfUserExists()" :disabled="userExists !== null && userExists !== false" v-model="credentials.email" type="email" required id="email" class="form-control form-control form-control-lg" style="max-width:400px;" placeholder="example@example.com">

                    <div class="mt-2 pt-2" v-if="userExists" style="max-width:400px;">
                        <p class="mt-2 medium-2"><strong>{{$trans('welcome_back')}}</strong></p>
                        <label for="email" class="medium">{{$trans('please_login_with_pass')}}:</label>
                        <input type="password" autocomplete="current-password" v-on:keyup.enter="login()" name="password" class="form-control form-control form-control-lg" style="width:100%;" :placeholder="$trans('Password')" v-model="credentials.password">    
                        <p><ui-a :href="'/password-reset/'" style="float:right;"> {{$trans('I forgot my password')}}</ui-a></p>

                        <ui-button :working="busy" class="mt-2" @click="login()" :label="$trans('Login')" />     
                    </div>
                </form>
                <div class="mt-2 pt-2" v-if="userExists === false">
                    <sign-up :email="credentials.email" :access-code="accessCode">
                    </sign-up>
                </div>

                <ui-button v-if="userExists === null" class="mt-4" @click="checkIfUserExists()" :label="$trans('Continue')" />     
            </div>


        </div>

    </div>

</template>

<script>
import backend from '../../backend.js'

import { useAuthStore } from '../../stores/auth'
import { useProfilesStore } from '../../stores/profiles'
import { mapStores } from 'pinia'

import signUp from './sign-up.vue'

export default {
    name: 'sign-in',
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
            busy: false
        }
    },
    components: {
        signUp
    },
    computed: {
        ...mapStores(useAuthStore, useProfilesStore)
    },
    props: {
        accessCode: {
            required: false,
            default: null
        },
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
        checkIfUserExists: function () {
            this.busy = true
            let urlEncodedEmail = encodeURIComponent(this.credentials.email)
            backend.get('/users/identifier/', null, {
                email: urlEncodedEmail
            }).then((response) => {
                this.busy = false
                this.userExists = response.data.exists
            })
        },
        login: function () {
            backend.post('/users/tokens/', this.credentials)
            .then(response => {
                this.authStore.setToken(response.data.token.string)
//                this.$stores.events.addEvent('user-login', {})
                this.$emit('closed')
            }).catch(error => {
                console.log(error)
            })
        },
        validateForm: function() {
//            this.$root.eventBus.$emit('doLogin', this.credentials, this.redirectPath)
        }
    }
}
</script>
