<template>

    <div>
        <div class="container">
            <div class="row py-2">
                <div class="col-lg-7 offset-lg-2 mt-5">
                    <h3 class="mb-1 medium">{{$trans('Joining')}}:</h3>
                    <h2 class="large mb-0">{{happening.name}}</h2>
                    <hr class="mt-0 mb-4">
                </div>

                <div v-if="!isUserAuthenticated" class="col-lg-7 offset-lg-2">

                    <sign-in inline-template>
                        <div>
                            <label for="email" class="medium">{{$trans('your_email')}}:</label>
                            <input :disabled="userExists !== null && userExists !== false" v-model="credentials.email" type="email" required pattern=".+@globex\.com" id="email" class="form-control form-control form-control-lg" style="max-width:400px;" placeholder="example@example.com"></input>

                            <div class="mt-2 pt-2" v-if="userExists" style="max-width:400px;">
                                <p class="mt-2 bold large">{{$trans('welcome_back')}}</p>
                                <p class="mt-2 bold">{{$trans('please_login_with_pass')}}.</p>
                                <label for="email" class="medium">{{$trans('your_password')}}:</label>
                                <input type="password" v-on:keyup.enter="validateForm()" name="password" class="form-control form-control form-control-lg" style="width:100%;" placeholder="${'Password'|trans}" v-model="credentials.password" v-validate.initial data-vv-rules="required">    
                                <p><a :href="rootUrl + '${lang}/password-reset/'" style="float:right;"> ${'I forgot my password'|trans}</a></p>

                                <ui-button class="mt-2" @click="validateForm()" :label="$trans('Login')" />     
                            </div>
                            <div class="mt-2 pt-2" v-if="userExists === false">
                                <sign-up :email="credentials.email" inline-template access="${access}">
                                    <div>
                                        <p class="mt-2 bold large">Looks like you are new here!</p>
                                        <label for="email" class="medium">Your name</label>
                                        <div class="pb-4">
                                            <input type="text" style="max-width:200px;" name="first_name" class="d-inline form-control form-control-lg" placeholder="${'First name'|trans}" v-model="credentials.first_name" v-validate.initial data-vv-rules="required|min:2" data-vv-as="first name">
                                            <input type="text" style="max-width:250px;" name="last_name" class="d-inline ml-2 form-control form-control-lg" placeholder="${'Last name'|trans}" v-model="credentials.last_name" v-validate.initial data-vv-rules="required|min:2" data-vv-as="last name">
        
                                        </div>

                                        <div v-if="errors.has('first_name') && showErrors" class="input-group-addon alert-danger">
                                            <span>{{ errors.first('first_name') }}</span>
                                        </div>
                                        <div v-if="errors.has('last_name') && showErrors" class="input-group-addon alert-danger">
                                            <span>{{ errors.first('last_name') }}</span>
                                        </div>
        
                                        <label for="email mt-4" class="medium">Please create a password:</label>
                                        <input type="password" name="password" class="form-control form-control form-control-lg"style="max-width:400px;" placeholder="${'New password'|trans}" v-model="credentials.password" v-validate.initial data-vv-rules="required">    

                                        <ui-button class="mt-4" @click="validateForm()" :label="$trans('Continue')" />

                                    </div>
                                </sign-up>
                            </div>

                            <ui-button v-if="userExists === null" class="mt-4" @click="checkIfUserExists()" :label="$trans('Continue')" />     
                        </div>
                    </sign-in>
                </div>

                <div v-if="isUserAuthenticated && !currentUserIsRegistered" class="col-lg-8 offset-lg-2">
                    <div v-for="(question, index) in info" class="mb-3">
                        <label class="mb-2">{{ info[index].q }}</label>

                        <div v-if="question.type == 'text'">
                            <div><input type="text" value="" :size="question.size" v-model="info[index].a"></div>
                        </div>

                        <div v-if="question.type == 'textarea'">
                            <div style="max-width:100%;width:500px;position:relative;">
                                <span style="position:absolute;right:0px;top:-30px;">{{question.a.length}} / {{question.maxlength}}</span>
                                <textarea-autosize :maxlength="question.maxlength" class="p-2 w-100" :rows="question.rows" value="" v-model="info[index].a"></textarea-autosize>

                            </div>
                        </div>

                        <div v-if="question.type == 'multiple'">

                            <div v-for="(choice, choiceIndex) in question.choices" class="mb-2">
                                <label class="form-check-label">
                                    <input type="radio" :value="choice" :id="index + '-' + choiceIndex" v-model="info[index].a"> {{choice}}
                                </label>
                            </div>
                        </div>
                        
                        <div v-if="question.type == 'checkbox'" class="mx-3">
                            <div v-for="(choice, choiceIndex) in question.choices">
                                <label :for="index + '-' + choiceIndex">  <input class="mr-2" type="checkbox" :value="choice" :id="index + '-' + choiceIndex" v-model="info[index].a"> {{choice}}</label>
                            </div>
                        </div>


                    </div>
                    <ui-button v-if="info.length == 0" v-on:click="register">{{$trans('Confirm')}}</ui-button>
                    <ui-button v-if="info.length > 0" v-on:click="register">{{$trans('Submit')}}</ui-button>


                </div>

                <div v-if="isUserAuthenticated && currentUserIsRegistered && stage !== 'fill-in-profile'" class="col-lg-7 offset-lg-2">
                    <p class="medium">{{$trans('you_have_been_reg_to')}} <a :href="'/' + $lang + '/group/' + happeningId + '/'">{{happening.name}}</a>.</p>
                    
                    <a :href="'/' + $lang + '/group/' + happeningId + '/'">
                        <ui-button class="mt-2" :label="$trans('Continue')" /></a>

                </div>

                <div v-if="stage === 'fill-in-profile'" class="col-lg-7 offset-lg-2">
                    ${include('happening/profile_wizard.html')}


                </div>

            </div>


        </div>

    </div>

</template>

<script>

    export default {
        name: 'group-register',
        data() {
            return {
                stage: 'register_form',
                step: 1,
                answers: [],
                info: []
            }
        },
        props: {
            currentUserIsRegistered: {
                required: false,
                type: Boolean,
                default: false
            },
            questions: {
                required: false,
                type: Array,
                default: function () {
                    return []
                }
            },
            happeningId: {
                required: true,
                type: String
            },
            apiUrl: {
                required: false,
                type: String,
                default: function () {
                    return this.$apiUrl
                }
            },
            language: {
                required: false,
                type: String
            },
            happening: {
                required: true,
                type: Object
            }
        },
        notifications: {
            registerNotification: {}
        },
        computed: {
            numberOfSteps: function () {
                return Math.max.apply(Math, this.questions.map(function(o) { return o.step }))
            }
        },
        watch: {
            questions:  {
                immediate: true, 
                handler: function (q) {
                    this.info = q
                }
            },
            step: function (newStep) {
                this.changeHash()
            },
            stage: function (newStep) {
                this.changeHash()
            }
        },
        methods: {
            hashChanged: function (hash) {
                let uri = new URL(document.URL).hash.substr(1)
                let position = uri.split('/')
                this.stage = position[0]
                this.step = position[1]
            },
            changeHash: function () {
                let urlOb = new URL(document.URL)
                urlOb.hash = '#' + this.stage + '/' + this.step
                let newUrl = urlOb.href
                document.location.href = newUrl
            },
            register: function () {
                if (this.info.length > 0) {
                    this.info.forEach(element => this.answers.push({q: element.q, a: element.a}))
                }
                this.$http.post(this.apiUrl + '/happenings/' + this.happeningId + '/registrations/',
                    {
                        'registration_info': this.answers
                    }, {
                        headers: {
                            'Authorization': this.getAuthorizationHeaderMixin()
                        }
                    }).then((response) => {
                        if (response.data.status === 'success') {
                            this.stage = 'thanks_for_registering'
                            this.$emit('happening-updated')
                        } else {
                            alert('Sorry, there has been an error submitting this application.')
                        }
                    },
                    (error) => {
                        console.log(error)
                        alert('Sorry, there has been an error submitting this application.')
                    })
            },
            apply: function () {
                this.info.forEach(element => this.answers.push({q: element.q, a: element.a}))
                this.$http.post(this.apiUrl + '/happenings/' + this.happeningId + '/applications/',
                    {
                        'application_info': this.answers
                    }, {
                        headers: {
                            'Authorization': this.getAuthorizationHeaderMixin()
                        }
                    }).then((response) => {
                        if (response.data.status === 'success') {
                            this.stage = 'thanks_for_applying'
                        } else {
                            alert('Sorry, there has been an error submitting this application.')
                        }
                    },
                    (error) => {
                        console.log(error)
                        alert('Sorry, there has been an error submitting this application.')
                    })
            },
            setup: function() {
                this.fetchHappening()
            },
            fetchHappening: function () {
            }
        },
        beforeDestroy () {
        },
        created: function() {
        },
        mounted: function() {
            // set this up
            this.setup()
//            window.addEventListener('hashchange', (e) => this.hashChanged(e))

            var self = this
            /*
            window.addEventListener('beforeunload', function (e) {
                if (!self.currentUserIsRegistered) {
                    if (self.stage !== 'thanks_for_applying' && self.stage !== 'thanks_for_registering') {
                        // Cancel the event
                        e.preventDefault()
                        e.returnValue = ''
                    }
                }
            })
            this.changeHash()
            */
        }
    }
</script>
