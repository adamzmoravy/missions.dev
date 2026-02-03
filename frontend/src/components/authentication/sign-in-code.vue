<template>

    <div class="col-md-4">
        <h2 class="xx-large mb-4 mt-4">{{$trans('Register')}}</h2>

        <div class="form-group">
            <div class="input-group" style="width:100%">
                <input type="text" name="first_name" class="form-control" :placeholder="$trans('Fist name')" v-model="credentials.first_name">
            </div>
        </div>
        <div class="form-group">
            <div class="input-group" style="width:100%">
                <input type="text" class="form-control" :placeholder="$trans('Last name')" name="last_name" v-model="credentials.last_name">
            </div>
        </div>
        <div class="form-group">
            <div class="input-group" style="width:100%">
                <input type="email" name="email" class="form-control" :placeholder="$trans('Email')" v-model="credentials.email">
            </div>
        </div>
        <div class="form-group">
            <div class="input-group" style="width:100%">
                <input type="password" name="password" class="form-control" :placeholder="$trans('Password') + ' (' + $trans('Min 6 characters') + ')'" v-model="credentials.password">
            </div>
        </div>
        <div class="form-group">
            <div class="input-group" style="width:100%">
                <input type="password" name="retype_password" class="form-control" :placeholder="$trans('Repeat password')" v-model="credentials.retype_password">
             </div>
        </div>
        <div class="form-group">
            <div>{{$trans('Already have an account?')}}
                <a @click="uiStore.login()">
                    {{$trans('Login')}}
                </a>
            </div>
        </div>    
        <div class="form-group">
            <p class="error-message" v-if="errorMessage">{{errorMessage}}</p>
            <ui-button @click="register()" label="Register"></ui-button>
        </div>
    </div>


</template>

<script>
import { useUIStore } from '../../stores/ui.js'
import { useAuthStore } from '../../stores/auth'
import { mapStores } from 'pinia'

import backend from '../../backend'

export default {
    name: 'sign-in-code',
    computed: {
        ...mapStores(useUIStore, useAuthStore)
    },
    methods: {
        register: function() {
            this.registerUser(this.credentials).then((response) => {
                this.processLogin(response.data.token.string, this.redirectPath)
            }, (error) => {
                alert(error.response.data.message)                
            })
        },
        registerUser: function (credentials) {
            credentials.access_code = this.accessCode

            return backend.post('/users/', credentials)

        },
        processLogin: function(apiUrl, data, redirect = false) {
            this.authStore.setToken(data)
            if (redirect) {
                // debug info
                this.openUrl(redirect)
            }
        }
    },
    data() {
        return {
            errorMessage: null,
            credentials: {
                email: '',
                first_name: '',
                last_name: '',
                password: '',
                retype_password: ''
            },
            showErrors: false
        }
    },
    props: {
        accessCode: {
            type: String,
            required: false,
            default: null
        }
    }
}
</script>
