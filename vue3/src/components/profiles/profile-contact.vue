<template>
    <div>
        <ui-button :label="$trans('Contact')" v-on:click="modalOpen = true" v-if="isUserAuthenticated" type="buttom" :disabled="this.requestSent"></ui-button>
        <ui-modal title="Request contact" :open="modalOpen" @close="modalOpen = false">

            <template #body>
                <div class="my-auto">

                    <p style="font-size:1em;">You can request contact with <strong>{{profile.first_name}} {{profile.last_name}}</strong>. They will receive an e-mail with your request, showing them your e-mail address.
                        </p><p style="font-size:0.8em;">You can only send a contact request once.</p>
                    <textarea v-model="message" class="form-control" maxlength="350" style="width:100%;height:80px;font-size:110%;" placeholder="Your short message with the contact request">

                    </textarea>

                    <p style="margin-top:10px;">Your e-mail address:<br><strong>{{userEmail}}</strong></p>

                </div>

            </template>

            <template #footer>

                <div class="d-flex justify-content-center">
                    <ui-button button-type="new" type="submit" v-on:click="sendContactRequest()" :disabled="requestSent" style="margin-top:15px;" class="float-right">
                       {{$trans('Send')}}</ui-button>

                </div>

            </template>

        </ui-modal>


    </div>

</template>

<script>

    export default {
        name: 'profile-contact',
        data() {
            return {
                modalOpen: false,
                requestSent: false,
                message: ''
            }
        },
        props: {
            // inherit global data from parent
            profile: {
                required: true,
                type: Object
            },
            apiUrl: {
                required: false,
                type: String,
                default: function () {
                    return this.$apiUrl
                }
            }
        },
        mixins: [
        ],
        notifications: {
            contactRequestSent: {}
        },
        methods: {
            sendContactRequest () {
                this.$http.post(this.apiUrl + '/users/' + this.profile.user_id + '/contact/',
                    {
                        'message': this.message
                    }, {
                        headers: {
                            'Authorization': this.getAuthorizationHeaderMixin()
                        }
                    }).then((response) => {
                        let message = response.data.message
                        this.contactRequestSent({
                            message: message,
                            timeout: 5000,
                            type: 'success'
                        })
                        this.modalOpen = false
                        this.requestSent = true
                    }, (error) => {
                        let message = error.response.data.message
                        if (error.response.data.status === 'rate_limit') {
                            message = 'You can only send this user a contact request once'
                        }
                        this.contactRequestSent({
                            message: message,
                            timeout: 5000,
                            type: 'error'
                        })
                    })
            },
            setup: function() {

            }
        },
        created: function() {
        },
        mounted: function() {

        }
    }
</script>
