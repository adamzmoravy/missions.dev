<template>

        <div v-if="step == 'password-reset'">
            <h4 class="mt-4 mb-4 medium-2">{{$trans('enter_email_password_reset')}}</h4>
            <form action="#" @submit.prevent="resetPassword">
                <div class="form-group">
                    <div class="input-group col-md-3 ml-0 pl-0">
                        <input type="email" name="email" class="form-control" :placeholder="$trans('Email')" v-model="email">
                    </div>
                </div>
                <ui-button :working="working" type="submit" :class="{success:isEmailModified, secondary:!isEmailModified}" :disabled="!isEmailModified || resetPasswordSuccessful">
                    {{$trans('Send')}}
                </ui-button>
            </form>
            <span v-if="resetPasswordSuccessful">
                <h4 class="mt-4 mb-4">
                    {{ $trans('password_reset_email_sent') }} 
                </h4></span>
        </div>

        <div v-if="step == 'password-change'" style="max-width: 600px;">

            <h4 class="medium-2">{{$trans('Enter your email address and choose a new password.')}}</h4>
            <form action="#" @submit.prevent="changePassword()" class="mb-5">
                <div class="form-group mt-4">
                    <div class="input-group" style="width:100%">
                        <div class="col-md-4">
                            <label>{{$trans('Email')}}:</label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" name="email" class="form-control" :placeholder="$trans('Email')" v-model="email">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group" style="width:100%">
                        <div class="col-md-4">
                            <label>{{$trans('New password')}}:</label>
                        </div>
                        <div class="col-md-8">
                            <input type="password" name="new_password" class="form-control" :placeholder="$trans('New password')" v-model="newPassword">
                        </div>

                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group" style="width:100%">
                        <div class="col-md-4">
                            <label>{{$trans('Repeat new password')}}:</label>
                        </div>
                        <div class="col-md-8">
                            <input type="password" name="retype_password" class="form-control" :placeholder="$trans('Repeat new password')" v-model="retypePassword">
                        </div>
                    </div>
                </div>
                <ui-button type="submit" :class="{success:isChangePasswordFormModified, secondary:!isChangePasswordFormModified}" :disabled="!isChangePasswordFormModified || responseStatus.changePassword == 'success'">
                    {{$trans('Set new')}}</ui-button>
            </form>
            <span v-if="passwordsDoNotMatch">{{ $trans('The passwords do not match. Please enter the password agin exactly as before.') }}</span>
            <span v-if="responseStatus.changePassword == 'success'">{{$trans('The password was modified successfully. Please')}}
                <a @click="$parent.loginAndRedirect"> {{$trans('log in')}}</a>
            {{ $trans('using the new password.') }}</span>
            <span v-if="responseStatus.changePassword == 'rate_limit'">{{$trans('There was an error, you may need to start the')}}
                <ui-a :href="'/password-reset/'">{{$trans('reset process')}}</ui-a>
                {{ $trans('again') }}
            </span>
        </div>


</template>

<script>
import { useUIStore } from '../stores/ui';
import { mapStores } from 'pinia'
import backend from '../backend'

export default {
    name: 'password-reset',
    data() {
        return {
            working: false,
            email: '',
            newPassword: '',
            retypePassword: '',
            resetPasswordSuccessful: false,
            passwordsDoNotMatch: false,
            responseStatus: {
                changePassword: ''
            }
        }
    },
    props: {
        step: {
            required: true
        },
        resetToken: {
            required: false,
            default: false
        },
        redirectUrl: { /* TODO: implement */
            required: false,
            default: false
        }
    },
    mixins: [
    ],
    methods: {
        setup: function() {
            if (this.resetToken) {
            } else {
            }
        },

        resetPassword: function() {
            this.working = true
            var resetPasswordBody = {
                email: this.email
            }

            backend.post('/users/password_resets/', resetPasswordBody).then((response) => {
                this.resetPasswordSuccessful = true
                this.working = false
            })

        },
        changePassword: function() {

            this.working = true

            if (this.newPassword !== this.retypePassword) {
                this.passwordsDoNotMatch = true
                return
            }

            let changePasswordBody = {
                email: this.email,
                password_reset_token: this.$route.params.token,
                new_password: this.newPassword
            }

            backend.post('/users/password_changes/', changePasswordBody).then((response) => {
                if (response.data.status == 'success') {
                    this.responseStatus.changePassword = 'success'
                    this.working = false

                }

            }, (error) => {
                console.log(error)
                this.working = false
            })
        }
    },
    computed: {
        resetToken: function() {
            return this.$route.params.token
        },
        ...mapStores(useUIStore),
        isEmailModified: function() {
            if (this.email !== '') {
                return true
            } else {
                return false
            }
        },
        isChangePasswordFormModified: function() {
            if (this.email !== '') {
                return true
            } else if (this.newPassword !== '') {
                return true
            } else {
                return false
            }
        }
    },
    mounted: function() {
        // set this up
        this.setup()
    }
}
</script>
