<template>
        <div class="row">
            <div class="col-md-12" v-if="!platformCreated">
                <h3 class="regular">Platform domain</h3>
                <div class="row mb-2">
                    <div class="col-md-4 pr-0">
                        <input id="platform_key" type="text" v-model="platform_key" class="form-control">             
                    </div>
                    <div class="col-md-6 pl-0">
                        <p class="large">.missions.dev</p>
                    </div>
                </div>
                <h3 class="regular">Admin account</h3>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label for="admin_email">Email</label>
                        <input id="admin_email" v-model="admin_email" type="text" class="form-control">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="admin_password">Password</label>
                        <input id="admin_password" v-model="admin_password" type="password" class="form-control">
                    </div>
                </div>


                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="admin_first_name">First name</label>
                        <input id="admin_first_name" v-model="admin_first_name" type="text" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="admin_las_name">Last name</label>
                        <input id="admin_last_name" v-model="admin_last_name" type="text" class="form-control">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col">
                        <ui-button @click="create" :disabled='!isValid' class="mt-2">
                            Create
                        </ui-button>
                    </div>
                </div>

            </div>
            <div class="col-md-12" v-else>
                <h3 class="regular">Platform created</h3>
                <p class="medium-2">Your platform has been created. You can access it at<br> 
                    <div class="url-container"><a :href="'https://' + platform_key + '.missions.dev/en/onboarding/'">https://{{platform_key}}.missions.dev</a></div>
                </p>    
                <p class="medium-2">You can now start a quick onboarding process. <a :href="'https://' + platform_key + '.missions.dev/en/onboarding/'">Click here to begin</a>.</p>
                <p class="medium-2">Use the admin account credentials you created earlier to log in.</p>
            </div>
        </div>
</template>

<style scoped>
.url-container {
background-color: #f8f9fa;
padding: 10px;
border: 1px solid #dee2e6;
border-radius: 5px;
margin-top: 10px;
}
</style>

<script>
    import backend from '../backend.js'
    export default {
        name: 'platform_create',
        data() {
            return {
                platformCreated: false,
                platform_key: null,
                admin_email: null,
                admin_password: null,
                admin_first_name: null,
                admin_last_name: null
            }
        },
        computed: {
            isValid: function () {
                // && this.admin_email !== null && this.admin_password !== null && this.admin_first_name !== null && this.admin_last_name !== null
                if (this.platform_key !== null && this.admin_email !== null && this.admin_password !== null && this.admin_first_name !== null && this.admin_last_name !== null) {
                    return true
                } else {
                    return false
                }
            }
        },
        props: {
        },
        mixins: [
        ],
        notifications: {
        },
        methods: {
            create: function() {
                return backend.post('/platform/', {
                    platform_key: this.platform_key,
                    admin_email: this.admin_email,
                    admin_password: this.admin_password,
                    admin_last_name: this.admin_last_name,
                    admin_first_name: this.admin_first_name
                }).then((response) => {
                    if (response && response.status >= 200 && response.status < 300) {
                        this.platformCreated = true
                    } else if (response && response.data && response.data.error) {
                        console.error('Platform creation failed:', response.data.error)
                    } else {
                        console.error('Platform creation failed', response)
                    }
//                    window.location.href = 'https://' + this.platform_key + '.missions.dev'
                })
            }
        },
        beforeDestroy () {
        },
        created: function() {
        },
        mounted: function() {
        }
    }
</script>
